# Axia MCP Server - n8n Integration

## ✅ Server Status

Der Axia MCP Server läuft jetzt **genau wie Notion MCP** über supergateway mit SSE!

- **SSE Endpoint**: `http://localhost:8102/sse-axia` (extern) oder `http://mcp-axia:8102/sse-axia` (intern)
- **Message Endpoint**: `http://localhost:8102/message`
- **Container**: `mcp-axia`

## n8n MCP Node konfigurieren

### Schritt 1: n8n öffnen

http://localhost:5678

### Schritt 2: MCP Tool Node hinzufügen

1. Neuen Workflow erstellen
2. Klicke auf **"+"**
3. Suche nach **"MCP Tool"** oder **"Model Context Protocol"**
4. Wähle den MCP Tool Node

### Schritt 3: MCP Server Connection einrichten

Im MCP Tool Node:

**Server URL**: `http://mcp-axia:8102`
**SSE Path**: `/sse-axia`
**Message Path**: `/message`

Oder kombiniert:
- **SSE Endpoint**: `http://mcp-axia:8102/sse-axia`

### Schritt 4: Tools auswählen

Nach dem Verbinden siehst du alle verfügbaren Tools:
- `get_user` - User & Company Daten abrufen
- `get_goals` - Goals mit KPIs auflisten
- `create_goal` - Neues Goal erstellen
- `get_runs` - Runs abrufen
- `create_todos` - Todos erstellen
- `analyze_todos` - Todos analysieren lassen

### Schritt 5: Tool verwenden

Wähle z.B. `get_goals`:
- **include_kpis**: `true`

Klicke **"Test step"** → Fertig! 🎉

## Beispiel: Goals in Slack posten

```
[Schedule Trigger: täglich 9:00]
    ↓
[MCP Tool: Axia - get_goals]
    ↓
[Slack: Post Message]
```

## Verfügbare Tools

### get_user
Gibt aktuellen User + Company zurück

**Parameter**:
- `include_company` (boolean, optional): Company Daten inkludieren

### get_goals
Liste aller Goals mit KPIs

**Parameter**:
- `include_kpis` (boolean, optional): KPIs pro Goal anzeigen

### create_goal
Neues SMART Goal erstellen

**Parameter**:
- `title` (string, required): Goal Titel
- `description` (string, required): Beschreibung
- `priority` (string, optional): "low" | "medium" | "high"
- `time_frame` (string, optional): Zeitrahmen z.B. "3 months"

### get_runs
Alle oder gefilterte Runs

**Parameter**:
- `limit` (number, optional): Max Anzahl
- `goal_id` (string, optional): Filter nach Goal

### create_todos
Todos erstellen und optional analysieren

**Parameter**:
- `goal_id` (string, required): Zugehöriges Goal
- `todos` (array[string], required): Liste von Todos
- `analyze` (boolean, optional): Direkt analysieren lassen

### analyze_todos
Existierende Todos analysieren

**Parameter**:
- `run_id` (string, required): Run ID

## Troubleshooting

### "Cannot connect to MCP server"

1. Check Container Status:
   ```bash
   docker ps | grep mcp-axia
   ```

2. Check Logs:
   ```bash
   docker logs mcp-axia
   ```

3. Sollte zeigen:
   ```
   [supergateway] Listening on port 8102
   [supergateway] SSE endpoint: http://localhost:8102/sse-axia
   ```

4. Restart wenn nötig:
   ```bash
   docker compose -f docker-compose.n8n.yaml restart mcp-axia
   ```

### "Tools not showing"

- Check ob SSE Endpoint erreichbar:
  ```bash
  curl http://localhost:8102/sse-axia
  ```
  
- Sollte eine SSE Verbindung öffnen (hängt)

### "Server restarting"

- Check Logs für Fehler:
  ```bash
  docker logs mcp-axia --tail 50
  ```

## Server Details

**Technologie**: 
- supergateway:uvx (gleich wie Notion & DuckDuckGo MCP)
- SSE (Server-Sent Events) Transport
- stdio → SSE Bridge

**Networks**:
- `n8n-network` - Kommunikation mit n8n
- `axia-shared-network` - Zugriff auf Laravel API

**Environment**:
- `AXIA_API_URL=http://axia-web-1/api`
- `AXIA_API_TOKEN` - Aus .env

## Das wars! 🚀

Dein Axia MCP Server funktioniert jetzt **exakt wie Notion MCP** in n8n.

Keine HTTP Requests, keine Code Nodes - einfach **MCP Tool Node** nutzen!
