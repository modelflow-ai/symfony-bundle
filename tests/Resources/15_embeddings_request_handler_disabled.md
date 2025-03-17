# Embeddings Request Handler Disabled Test

## Configuration

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: "%env(OPENAI_API_KEY)%"

    embeddings:
        generators:
            default:
                enabled: true
                provider: "openai"
                model: "text-embedding-ada-002"
        stores:
            default:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/default'
        request_handler:
            enabled: false
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    # Default Embedding Generator
    modelflow_ai.embeddings.default.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
    modelflow_ai.embeddings.default.splitter:
        class: ModelflowAi\Embeddings\Splitter\EmbeddingSplitter
    modelflow_ai.embeddings.default.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.default.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator

    # Vector Stores
    modelflow_ai.embeddings.store.default:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface

    # Embedding Store Factory
    modelflow_ai.embeddings_store_factory: ~

aliases:
    modelflow_ai.embeddings.default_generator: 'modelflow_ai.embeddings.default.generator'
    modelflow_ai.embeddings.default_store: 'modelflow_ai.embeddings.store.default'

not_services:
    # These services should not exist when request_handler is disabled
    modelflow_ai.embeddings.store_handler: ~
    modelflow_ai.embeddings.similarity_handler: ~
    modelflow_ai.embeddings.request_handler: ~

not_aliases:
    # Make sure the ModelflowAi\Embeddings\EmbeddingsRequestHandlerInterface alias doesn't exist
    ModelflowAi\Embeddings\EmbeddingsRequestHandlerInterface: ~
```
