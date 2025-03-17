# Embeddings Request Handler With No Generator

This test case verifies that an exception is thrown when the embeddings request handler is enabled without any generators being configured.

## Configuration

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: "%env(OPENAI_API_KEY)%"

    embeddings:
        stores:
            default:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/default'
        request_handler:
            enabled: true
            mapping:
                'App\Entity\Document':
                    key: 'default'
```

## Expects

```yaml
exception:
    message: "EmbeddingsRequestHandler is enabled but no embedding generator is configured"
```