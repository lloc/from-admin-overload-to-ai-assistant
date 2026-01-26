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
npm -g install @wordpress/env
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

Configure your MCP client (Claude Code, VS Code, Cursor, etc.):

Generate the base64-encoded credentials:

```bash
echo -n "username:application-password" | base64
```

Configure your MCP client:

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

**Example request:**

```bash
# Using the MCP adapter execute ability
{
  "ability_name": "site-health/check",
  "parameters": {
    "category": "performance"
  }
}
```

**Example response:**

```json
{
  "success": true,
  "data": {
    "summary": {
      "good": 5,
      "recommended": 2,
      "critical": 0
    },
    "results": [
      {
        "test": "php_version",
        "label": "Your site is running the current version of PHP (8.3)",
        "status": "good",
        "category": "performance"
      }
    ]
  }
}
```

**Required capability:** `manage_options`

## License

GPL-2.0-or-later
