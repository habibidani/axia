# n8n Chat Integration für Axia

## ✅ Installation abgeschlossen

Der n8n Chat ist jetzt in die Laravel App integriert!

### Was wurde installiert:

1. **@n8n/chat Package** (npm)
2. **Chat JavaScript** (`resources/js/chat.js`)
3. **n8n Workflow** (`n8n-workflows/axia-chat-agent.json`)
4. **Konfiguration** in `config/services.php`

---

## Setup in n8n

### Schritt 1: Workflow importieren

1. Öffne n8n: http://localhost:5678
2. Klicke auf **"+ New workflow"**
3. **"..." → Import from File**
4. Wähle: `/home/nileneb/axia/n8n-workflows/axia-chat-agent.json`
5. Workflow wird importiert mit:
   - Chat Webhook
   - AI Agent mit Axia MCP Tools
   - OpenAI Chat Model
   - Window Buffer Memory (für Conversation History)

### Schritt 2: OpenAI API Key konfigurieren

Im Workflow:
1. Klicke auf **"OpenAI Chat Model"** Node
2. Klicke auf **"Credential to connect with"**
3. Wähle **"Create New Credential"**
4. Gib deinen OpenAI API Key ein
5. Klicke **"Save"**

### Schritt 3: Workflow aktivieren

1. Klicke auf **"Inactive"** Toggle oben rechts → wird zu **"Active"**
2. Kopiere die **Webhook URL** (z.B. `https://n8n.getaxia.de/webhook/chat`)

### Schritt 4: Webhook URL in .env setzen

```bash
# .env
N8N_CHAT_WEBHOOK_URL=https://n8n.getaxia.de/webhook/chat
```

Oder in Production:
```bash
N8N_CHAT_WEBHOOK_URL=https://n8n.getaxia.de/webhook-test/axia-chat
```

---

## Chat Widget testen

### Schritt 1: Laravel Assets neu bauen

```bash
npm run build
# oder für Development:
npm run dev
```

### Schritt 2: Laravel App öffnen

```bash
http://localhost:6478
```

### Schritt 3: Chat Widget sollte erscheinen

- Unten rechts auf der Seite
- Klick auf das Chat Icon
- Chat Fenster öffnet sich
- Tippe eine Nachricht: "What are my current goals?"

### Erwartete Antwort:

Der AI Agent nutzt die **Axia MCP Tools** um:
1. Deine Goals abzurufen (`get_goals`)
2. User/Company Info zu holen (`get_user`)
3. Runs anzuzeigen (`get_runs`)

Beispiel:
```
You currently have 1 goal:

📈 Increase MRR by 50%
Priority: High
Status: Active

This goal has 3 KPIs tracking your progress.
Would you like me to analyze your current tasks against this goal?
```

---

## Chat Agent Capabilities

Der Chat Agent hat Zugriff auf alle **6 Axia MCP Tools**:

### 1. **get_user**
```
User: "Who am I?"
Agent: *calls get_user tool*
       "You're John Doe, CEO at Acme Inc. You have 2 co-founders and 8 employees."
```

### 2. **get_goals**
```
User: "Show my goals"
Agent: *calls get_goals with include_kpis=true*
       "You have 3 active goals: 1) Increase MRR by 50% (high priority)..."
```

### 3. **create_goal**
```
User: "I want to hire 5 engineers by Q2"
Agent: *calls create_goal*
       "Created new goal: Hire 5 engineers by Q2 2025 (ID: xxx)"
```

### 4. **get_runs**
```
User: "What was my last focus score?"
Agent: *calls get_runs with limit=1*
       "Your last focus score was 73/100 from yesterday's analysis."
```

### 5. **create_todos**
```
User: "Analyze these tasks: 1) Review metrics 2) Hire engineer 3) Update deck"
Agent: *calls create_todos with analyze=true*
       "I've analyzed your tasks. The hiring task is HIGH priority aligned with your recruitment goal..."
```

### 6. **analyze_todos**
```
User: "Re-analyze my last run"
Agent: *calls analyze_todos with run_id*
       "Re-analysis complete. Your focus score improved to 78/100..."
```

---

## Workflow Anpassungen (Optional)

### System Prompt ändern

Im **AI Agent (Chat)** Node → **System Message**:

```
You are axia, an AI focus coach for startup founders.

Your personality:
- Friendly and encouraging
- Direct and action-oriented
- Data-driven but empathetic

You help users:
1. Prioritize tasks based on goals
2. Create SMART goals
3. Understand their metrics
4. Make better decisions

Available tools: [list of 6 MCP tools]

Always ask clarifying questions if user intent is unclear.
```

### Temperatur anpassen

**AI Agent (Chat)** → **Options** → **Temperature**:
- `0.7` (default) - Balanced
- `0.3` - More focused, less creative
- `0.9` - More creative, less focused

### Memory Window ändern

**Window Buffer Memory** Node:
- **Window Size**: Anzahl der letzten Nachrichten (default: 5)
- Erhöhen für längere Konversationen

---

## Chat Widget Customization

In `resources/js/chat.js` kannst du anpassen:

### Farben
```javascript
theme: {
    primaryColor: '#ee4769',        // Main brand color
    messageUserBackground: '#ee4769', // User message bubble
    messageBotBackground: '#f9fafb',  // Bot message bubble
}
```

### Position
```javascript
mode: 'window',              // 'window' = popup, 'fullscreen' = full page
showWindowCloseButton: true, // Show X button
```

### Initial Messages
```javascript
initialMessages: [
    {
        text: 'Your custom welcome message',
        sender: 'bot',
    },
],
```

### Placeholder
```javascript
chatInputPlaceholder: 'Type your message...',
```

---

## Debugging

### Chat Widget erscheint nicht?

1. **Check Console** (F12):
   ```javascript
   // Should see:
   n8n chat initialized
   ```

2. **Check Assets**:
   ```bash
   npm run build
   # Restart browser
   ```

3. **Check Webhook URL**:
   ```bash
   echo $N8N_CHAT_WEBHOOK_URL
   ```

### Agent antwortet nicht?

1. **Check n8n Workflow**:
   - Ist Workflow **Active**?
   - Check Workflow **Executions** Tab

2. **Check Webhook**:
   ```bash
   curl -X POST https://n8n.getaxia.de/webhook/chat \
     -H "Content-Type: application/json" \
     -d '{"message":"test","sessionId":"test-123"}'
   ```

3. **Check MCP Server**:
   ```bash
   docker logs mcp-axia
   # Should show SSE connections
   ```

### Agent kann Tools nicht nutzen?

1. **Check Axia MCP Tools Node** in Workflow:
   - Base URL: `http://mcp-axia:8102`
   - SSE Path: `/sse-axia`

2. **Test MCP Server**:
   ```bash
   curl http://localhost:8102/sse-axia
   # Should open SSE connection
   ```

---

## Production Deployment

### 1. Set Production Webhook URL

```bash
# .env.production
N8N_CHAT_WEBHOOK_URL=https://n8n.getaxia.de/webhook/chat
```

### 2. Build Assets

```bash
npm run build
```

### 3. Deploy Workflow to n8n Production

1. Export Workflow from Dev
2. Import to Production n8n
3. Set OpenAI Credentials
4. Activate Workflow
5. Copy Production Webhook URL
6. Update `.env`

---

## Advanced: Custom Tools

Du kannst dem Chat Agent **zusätzliche Tools** geben:

### Beispiel: Calculator Tool

Im Workflow, füge **Calculator** Tool Node hinzu:

1. **"+"** → **AI** → **Calculator**
2. Verbinde mit **AI Agent (Chat)**
3. Agent kann jetzt rechnen:
   ```
   User: "If I grow MRR by 20% monthly for 6 months, what's the end MRR?"
   Agent: *uses calculator*
          "Starting at 10k, growing 20% monthly for 6 months = 29.86k MRR"
   ```

### Beispiel: Web Search Tool

1. **"+"** → **AI** → **Serper** (Google Search)
2. Verbinde mit **AI Agent (Chat)**
3. Agent kann jetzt suchen:
   ```
   User: "What's the average SaaS churn rate?"
   Agent: *searches web*
          "According to recent data, average SaaS churn is 5-7% monthly..."
   ```

---

## Das wars! 🎉

Dein **Axia Chat Agent** ist jetzt live und kann:

✅ Mit Usern chatten
✅ Alle 6 Axia MCP Tools nutzen
✅ Conversation History merken
✅ In der Laravel App embedded werden

**Next Steps:**
1. n8n öffnen → Workflow importieren
2. OpenAI API Key setzen
3. Workflow aktivieren
4. Laravel App öffnen
5. Chat testen!

Bei Problemen, check die **Debugging** Section oben.
