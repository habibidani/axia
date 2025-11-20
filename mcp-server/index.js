#!/usr/bin/env node
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import fetch from 'node-fetch';
import { z } from 'zod';
import dotenv from 'dotenv';

dotenv.config();

const AXIA_API_URL = process.env.AXIA_API_URL || 'http://axia-php-fpm-1/api';
const AXIA_API_TOKEN = process.env.AXIA_API_TOKEN;
const MCP_SHARED_SECRET = process.env.MCP_SHARED_SECRET;

if (!AXIA_API_TOKEN) {
  throw new Error('AXIA_API_TOKEN environment variable is required');
}

if (!MCP_SHARED_SECRET) {
  throw new Error('MCP_SHARED_SECRET environment variable is required');
}

// Helper function for API calls
async function callAxiaAPI(endpoint, method = 'GET', body = null) {
  const options = {
    method,
    headers: {
      'Authorization': `Bearer ${AXIA_API_TOKEN}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(`${AXIA_API_URL}${endpoint}`, options);
  
  if (!response.ok) {
    const error = await response.text();
    throw new Error(`Axia API Error (${response.status}): ${error}`);
  }

  return response.json();
}

// Helper function for internal MCP API calls (with shared secret)
async function callInternalMcpAPI(endpoint, body) {
  const bodyStr = JSON.stringify(body);
  
  const options = {
    method: 'POST',
    headers: {
      'X-MCP-Secret': MCP_SHARED_SECRET,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: bodyStr,
  };

  const response = await fetch(`${AXIA_API_URL}/internal/mcp${endpoint}`, options);
  
  if (!response.ok) {
    const error = await response.text();
    throw new Error(`MCP Internal API Error (${response.status}): ${error}`);
  }

  return response.json();
}

// Zod schemas for tool inputs
const GetGoalsSchema = z.object({
  include_kpis: z.boolean().optional().default(true),
});

const CreateGoalSchema = z.object({
  title: z.string().min(1),
  description: z.string().optional(),
  priority: z.enum(['low', 'medium', 'high']).optional().default('medium'),
  time_frame: z.string().optional(),
});

const GetRunsSchema = z.object({
  limit: z.number().optional().default(10),
  goal_id: z.string().optional(),
});

const CreateTodoSchema = z.object({
  goal_id: z.string(),
  todos: z.array(z.string()).min(1),
  analyze: z.boolean().optional().default(true),
});

const GetUserSchema = z.object({
  include_company: z.boolean().optional().default(true),
});

// MCP Server
const server = new Server(
  {
    name: 'axia-mcp-server',
    version: '1.0.0',
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  }
);

// List available tools
server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: [
    {
      name: 'get_user',
      description: 'Get the current user profile and company information',
      inputSchema: {
        type: 'object',
        properties: {
          include_company: {
            type: 'boolean',
            description: 'Include company details in response',
            default: true,
          },
        },
      },
    },
    {
      name: 'get_goals',
      description: 'List all goals with optional KPIs',
      inputSchema: {
        type: 'object',
        properties: {
          include_kpis: {
            type: 'boolean',
            description: 'Include KPI data for each goal',
            default: true,
          },
        },
      },
    },
    {
      name: 'create_goal',
      description: 'Create a new business goal',
      inputSchema: {
        type: 'object',
        properties: {
          title: {
            type: 'string',
            description: 'Goal title (SMART format recommended)',
          },
          description: {
            type: 'string',
            description: 'Detailed description of the goal',
          },
          priority: {
            type: 'string',
            enum: ['low', 'medium', 'high'],
            description: 'Priority level',
            default: 'medium',
          },
          time_frame: {
            type: 'string',
            description: 'Time frame for achieving the goal (e.g., "6 months")',
          },
        },
        required: ['title'],
      },
    },
    {
      name: 'get_runs',
      description: 'Get analysis runs (todo evaluations) with optional filtering',
      inputSchema: {
        type: 'object',
        properties: {
          limit: {
            type: 'number',
            description: 'Maximum number of runs to return',
            default: 10,
          },
          goal_id: {
            type: 'string',
            description: 'Filter runs by specific goal ID',
          },
        },
      },
    },
    {
      name: 'create_todos',
      description: 'Create todos for a goal and optionally analyze them with AI',
      inputSchema: {
        type: 'object',
        properties: {
          goal_id: {
            type: 'string',
            description: 'ID of the goal these todos belong to',
          },
          todos: {
            type: 'array',
            items: { type: 'string' },
            description: 'Array of todo descriptions',
          },
          analyze: {
            type: 'boolean',
            description: 'Run AI analysis on the todos',
            default: true,
          },
        },
        required: ['goal_id', 'todos'],
      },
    },
    {
      name: 'analyze_todos',
      description: 'Get AI analysis and recommendations for a specific run',
      inputSchema: {
        type: 'object',
        properties: {
          run_id: {
            type: 'string',
            description: 'ID of the run to analyze',
          },
        },
        required: ['run_id'],
      },
    },
  ],
}));

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    switch (name) {
      case 'get_user': {
        const validated = GetUserSchema.parse(args);
        const endpoint = validated.include_company ? '/user/company' : '/user';
        const data = await callAxiaAPI(endpoint);
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(data, null, 2),
            },
          ],
        };
      }

      case 'get_goals': {
        const validated = GetGoalsSchema.parse(args);
        const data = await callAxiaAPI('/goals');
        
        let responseText = `Found ${data.data.length} goals:\n\n`;
        
        data.data.forEach((goal, index) => {
          responseText += `${index + 1}. ${goal.title}\n`;
          responseText += `   ID: ${goal.id}\n`;
          responseText += `   Priority: ${goal.priority}\n`;
          responseText += `   Status: ${goal.is_active ? 'Active' : 'Inactive'}\n`;
          
          if (validated.include_kpis && goal.kpis && goal.kpis.length > 0) {
            responseText += `   KPIs:\n`;
            goal.kpis.forEach(kpi => {
              responseText += `     - ${kpi.metric}: ${kpi.current_value}/${kpi.target_value} ${kpi.unit}\n`;
            });
          }
          responseText += `\n`;
        });

        return {
          content: [
            {
              type: 'text',
              text: responseText,
            },
          ],
        };
      }

      case 'create_goal': {
        const validated = CreateGoalSchema.parse(args);
        const data = await callAxiaAPI('/goals', 'POST', validated);
        return {
          content: [
            {
              type: 'text',
              text: `Goal created successfully!\nID: ${data.data.id}\nTitle: ${data.data.title}`,
            },
          ],
        };
      }

      case 'get_runs': {
        const validated = GetRunsSchema.parse(args);
        let endpoint = `/runs?limit=${validated.limit}`;
        if (validated.goal_id) {
          endpoint += `&goal_id=${validated.goal_id}`;
        }
        
        const data = await callAxiaAPI(endpoint);
        
        let responseText = `Found ${data.data.length} runs:\n\n`;
        
        data.data.forEach((run, index) => {
          responseText += `${index + 1}. Run #${run.id}\n`;
          responseText += `   Goal: ${run.goal?.title || 'Unknown'}\n`;
          responseText += `   Status: ${run.status}\n`;
          responseText += `   Todos: ${run.todos_count || 0}\n`;
          responseText += `   Created: ${new Date(run.created_at).toLocaleDateString()}\n\n`;
        });

        return {
          content: [
            {
              type: 'text',
              text: responseText,
            },
          ],
        };
      }

      case 'create_todos': {
        const validated = CreateTodoSchema.parse(args);
        const endpoint = validated.analyze ? '/todos' : '/todos/batch';
        const data = await callAxiaAPI(endpoint, 'POST', validated);
        
        let responseText = `Todos created successfully!\n`;
        responseText += `Run ID: ${data.data.run?.id || data.data.id}\n`;
        responseText += `Status: ${data.data.run?.status || data.data.status}\n`;
        
        if (validated.analyze && data.data.run?.analysis_summary) {
          responseText += `\nAI Analysis:\n${data.data.run.analysis_summary}`;
        }

        return {
          content: [
            {
              type: 'text',
              text: responseText,
            },
          ],
        };
      }

      case 'analyze_todos': {
        const { run_id } = args;
        const run = await callAxiaAPI(`/runs/${run_id}`);
        const todos = await callAxiaAPI(`/runs/${run_id}/todos`);
        
        let responseText = `Analysis for Run #${run_id}:\n\n`;
        responseText += `Goal: ${run.data.goal?.title}\n`;
        responseText += `Status: ${run.data.status}\n`;
        responseText += `Total Todos: ${todos.data.length}\n\n`;
        
        if (run.data.analysis_summary) {
          responseText += `Summary:\n${run.data.analysis_summary}\n\n`;
        }
        
        responseText += `Todos:\n`;
        todos.data.forEach((todo, index) => {
          responseText += `${index + 1}. ${todo.description}\n`;
          if (todo.evaluation) {
            responseText += `   Impact Score: ${todo.evaluation.impact_score}/10\n`;
            responseText += `   Recommendation: ${todo.evaluation.recommendation}\n`;
            responseText += `   Reason: ${todo.evaluation.reason}\n`;
          }
          responseText += `\n`;
        });

        return {
          content: [
            {
              type: 'text',
              text: responseText,
            },
          ],
        };
      }

      default:
        throw new Error(`Unknown tool: ${name}`);
    }
  } catch (error) {
    return {
      content: [
        {
          type: 'text',
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

// List available resources
server.setRequestHandler(ListResourcesRequestSchema, async () => ({
  resources: [
    {
      uri: 'axia://user',
      name: 'Current User Profile',
      description: 'Get the authenticated user profile and company',
      mimeType: 'application/json',
    },
    {
      uri: 'axia://goals',
      name: 'All Goals',
      description: 'List all business goals with KPIs',
      mimeType: 'application/json',
    },
    {
      uri: 'axia://runs/recent',
      name: 'Recent Analysis Runs',
      description: 'Get the 10 most recent todo analysis runs',
      mimeType: 'application/json',
    },
  ],
}));

// Handle resource reads
server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  try {
    switch (uri) {
      case 'axia://user': {
        const data = await callAxiaAPI('/user/company');
        return {
          contents: [
            {
              uri,
              mimeType: 'application/json',
              text: JSON.stringify(data, null, 2),
            },
          ],
        };
      }

      case 'axia://goals': {
        const data = await callAxiaAPI('/goals');
        return {
          contents: [
            {
              uri,
              mimeType: 'application/json',
              text: JSON.stringify(data, null, 2),
            },
          ],
        };
      }

      case 'axia://runs/recent': {
        const data = await callAxiaAPI('/runs?limit=10');
        return {
          contents: [
            {
              uri,
              mimeType: 'application/json',
              text: JSON.stringify(data, null, 2),
            },
          ],
        };
      }

      default:
        throw new Error(`Unknown resource: ${uri}`);
    }
  } catch (error) {
    throw new Error(`Failed to read resource: ${error.message}`);
  }
});

// Start server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error('Axia MCP Server running on stdio');
}

main().catch((error) => {
  console.error('Server error:', error);
  process.exit(1);
});
