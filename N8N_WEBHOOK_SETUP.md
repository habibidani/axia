# n8n Webhook Konfiguration für Laravel

## ✅ Laravel Status
- WebhookAiService deployed
- ChatController deployed
- Kein OpenAI-Key in .env
- **ALLE AI-Calls gehen über EIN Webhook: ai-analysis**

## 🔧 n8n Workflow zu konfigurieren

### AI Analysis Workflow (EINZIGER WORKFLOW!)
**Webhook:** `https://n8n.getaxia.de/webhook/ai-analysis`
**Method:** POST
**Input Body:**
```json
{
  "task": "todo_analysis|company_extraction|goals_extraction|chat",
  "system_message": "System prompt",
  "user_prompt": "User message",
  "temperature": 0.7,
  "run_id": "uuid (optional)",
  "company_id": "uuid (optional)",
  "session_id": "uuid (nur für chat)",
  "user_id": "uuid (nur für chat)"
}
```

**Workflow Aufbau:**
1. **Webhook Trigger** (POST, path: `/webhook/ai-analysis`)
2. **Switch Node** auf `{{ $json.task }}`:
   - **todo_analysis**
   - **company_extraction**
   - **goals_extraction**
   - **chat**
3. **Für jeden Branch: OpenAI Chat Node**
   - Model: gpt-4 oder gpt-3.5-turbo
   - System Message: `{{ $json.system_message }}`
   - User Message: `{{ $json.user_prompt }}`
   - Temperature: `{{ $json.temperature }}`
4. **Response Formatting Node (Code/Set)**
   ```javascript
   return {
     success: true,
     data: $input.item.json.choices[0].message.content,
     tokens_used: $input.item.json.usage.total_tokens
   };
   ```

**Output Format (IMMER):**
```json
{
  "success": true,
  "data": "AI response text or JSON object",
  "tokens_used": 1234
}
```

Bei Fehler:
```json
{
  "success": false,
  "error": "Error message"
}
```

## 🧪 Test Commands

### Test Todo Analysis
```bash
curl -X POST https://n8n.getaxia.de/webhook/ai-analysis \
  -H "Content-Type: application/json" \
  -d '{
    "task": "todo_analysis",
    "system_message": "You are a business advisor",
    "user_prompt": "Analyze these todos: 1. Ship feature 2. Fix bug",
    "temperature": 0.7
  }'
```

### Test Chat
```bash
curl -X POST https://n8n.getaxia.de/webhook/ai-analysis \
  -H "Content-Type: application/json" \
  -d '{
    "task": "chat",
    "system_message": "You are a helpful AI assistant",
    "user_prompt": "What is the capital of Germany?",
    "temperature": 0.7,
    "session_id": "test-123",
    "user_id": "1"
  }'
```

### Test Company Extraction
```bash
curl -X POST https://n8n.getaxia.de/webhook/ai-analysis \
  -H "Content-Type: application/json" \
  -d '{
    "task": "company_extraction",
    "system_message": "Extract company info",
    "user_prompt": "We are a B2B SaaS startup with 3 founders...",
    "temperature": 0.5
  }'
```

## ⚠️ WICHTIG
- **NUR DIESER EINE Webhook wird verwendet!**
- Der alte agent_webhook ist NICHT mehr in Verwendung
- Alle Tasks (Analysis + Chat) gehen über `/webhook/ai-analysis`
- OpenAI-Credentials müssen in n8n hinterlegt sein
