# n8n Setup für getaxia.de

## Übersicht

Diese Konfiguration stellt n8n mit folgenden MCP-Servern bereit:
- **DuckDuckGo MCP** - Websuche über DuckDuckGo
- **Notion MCP** - Integration mit Notion

## Ports

- **5678**: n8n Web-Interface
- **8100**: DuckDuckGo MCP Server (SSE)
- **8101**: Notion MCP Server (SSE)

## Setup

### 1. Umgebungsvariablen konfigurieren

In der `.env` Datei müssen folgende Variablen gesetzt werden:

```bash
# n8n Datenbank Passwort (sicheres Passwort wählen)
N8N_DB_PASSWORD=dein_sicheres_passwort

# Notion Integration Token (von https://www.notion.so/my-integrations)
NOTION_TOKEN=ntn_XXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

### 2. n8n starten

```bash
# n8n Stack starten
docker-compose -f docker-compose.n8n.yaml up -d

# Logs anzeigen
docker-compose -f docker-compose.n8n.yaml logs -f

# n8n stoppen
docker-compose -f docker-compose.n8n.yaml down
```

### 3. Zugriff

Nach dem Start ist n8n erreichbar unter:
- Lokal: http://localhost:5678
- Öffentlich (über SSL-Proxy): https://n8n.getaxia.de

## MCP Server Endpoints

Die MCP-Server sind über SSE (Server-Sent Events) erreichbar:

- **DuckDuckGo**: http://localhost:8100/sse-duckduckgo
- **Notion**: http://localhost:8101/sse-notion

## Notion Token erstellen

1. Gehe zu https://www.notion.so/my-integrations
2. Erstelle eine neue Integration
3. Kopiere das "Internal Integration Token"
4. Füge es in die `.env` Datei ein als `NOTION_TOKEN`
5. Teile die gewünschten Notion-Seiten/Datenbanken mit der Integration

## SSL-Proxy Konfiguration

Für den Zugriff über https://n8n.getaxia.de muss dein SSL-Proxy (z.B. Nginx Proxy Manager oder Traefik) konfiguriert werden:

- **Domain**: n8n.getaxia.de
- **Forward Hostname/IP**: localhost oder Server-IP
- **Forward Port**: 5678
- **Websockets Support**: Aktivieren
- **SSL**: Let's Encrypt Zertifikat

## Persistente Daten

Die Daten werden in Docker Volumes gespeichert:
- `n8n_data`: n8n Workflows und Konfiguration
- `n8n_pg_data`: PostgreSQL Datenbank

## Troubleshooting

### Container-Status prüfen
```bash
docker-compose -f docker-compose.n8n.yaml ps
```

### Logs anzeigen
```bash
# Alle Services
docker-compose -f docker-compose.n8n.yaml logs -f

# Nur n8n
docker-compose -f docker-compose.n8n.yaml logs -f n8n

# Nur MCP Server
docker-compose -f docker-compose.n8n.yaml logs -f mcp-duckduckgo mcp-notion
```

### Volumes löschen (Vorsicht: Alle Daten gehen verloren!)
```bash
docker-compose -f docker-compose.n8n.yaml down -v
```

## MCP Server in n8n verwenden

1. Öffne n8n im Browser
2. Erstelle einen neuen Workflow
3. Füge einen HTTP Request Node hinzu
4. Konfiguriere den Request:
   - **Method**: GET
   - **URL**: http://mcp-duckduckgo:8100/sse-duckduckgo (oder mcp-notion:8101/sse-notion)
   - **Accept**: text/event-stream

Die MCP-Server kommunizieren über Server-Sent Events (SSE) und können so in n8n-Workflows integriert werden.
