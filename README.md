# From Admin Overload to AI Assistant

Scale WordPress with intelligence.

## Requirements

- PHP >= 8.3
- WordPress >= 6.9

## Installation

```bash
composer install
```

## Development

Install wp-env globally:

```bash
npm install -g @wordpress/env
```

Start the local environment:

```bash
wp-env start
```

## Setup

1. Set permalinks to "Post name" under Settings > Permalinks
2. Create an Application Password under Users > Profile > Application Passwords

## MCP Configuration

This project uses the [MCP Adapter](https://github.com/WordPress/mcp-adapter) to expose WordPress abilities to AI agents via the Model Context Protocol.

### 1. Generate credentials

```bash
echo -n "username:application-password" | base64
```

### 2. Connect your AI client

#### Claude Code (CLI)

Add the server to your project (stored in `.mcp.json`):

```bash
claude mcp add --transport http --scope project \
  --header "Authorization: Basic <base64>" \
  wordpress http://localhost:8888/wp-json/mcp/mcp-adapter-default-server
```

Or add it to your user config (available across all projects):

```bash
claude mcp add --transport http --scope user \
  --header "Authorization: Basic <base64>" \
  wordpress http://localhost:8888/wp-json/mcp/mcp-adapter-default-server
```

#### Claude Desktop

Edit `~/Library/Application Support/Claude/claude_desktop_config.json` (macOS) or `%APPDATA%\Claude\claude_desktop_config.json` (Windows):

```json
{
  "mcpServers": {
    "wordpress": {
      "type": "http",
      "url": "http://localhost:8888/wp-json/mcp/mcp-adapter-default-server",
      "headers": {
        "Authorization": "Basic <base64>"
      }
    }
  }
}
```

#### Other MCP clients (VS Code, Cursor, etc.)

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "npx",
      "args": ["-y", "@anthropic-ai/mcp-proxy@latest", "--allowHttp"],
      "env": {
        "MCP_PROXY_URL": "http://localhost:8888/wp-json/mcp/mcp-adapter-default-server",
        "MCP_PROXY_HEADER_AUTHORIZATION": "Basic <base64>"
      }
    }
  }
}
```

## Available Abilities

### site-health/check

Run WordPress Site Health checks and return results grouped by status.

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `category` | string | No | Filter by category: `security`, `performance`, or empty string for all |

**Example response:**

```json
{
  "summary": {
    "good": 15,
    "recommended": 3,
    "critical": 0
  },
  "results": [
    {
      "test": "php_version",
      "label": "Your site is running the current version of PHP (8.3)",
      "status": "good",
      "category": ""
    }
  ]
}
```

**Required capability:** `manage_options`

## License

GPL-2.0-or-later