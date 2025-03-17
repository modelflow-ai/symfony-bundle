# Embeddings Request Handler With No Mapping

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
                splitter:
                    max_length: 1500
                    separator: " "
        stores:
            default:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/default'
        request_handler:
            enabled: true
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

    # Embeddings Request Handler and dependencies
    modelflow_ai.embeddings.store_handler:
        class: ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler
    modelflow_ai.embeddings.similarity_handler:
        class: ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler
    modelflow_ai.embeddings.request_handler:
        class: ModelflowAi\Embeddings\EmbeddingsRequestHandler

aliases:
    modelflow_ai.embeddings.default_generator: 'modelflow_ai.embeddings.default.generator'
    modelflow_ai.embeddings.default_store: 'modelflow_ai.embeddings.store.default'
    ModelflowAi\Embeddings\EmbeddingsRequestHandlerInterface: 'modelflow_ai.embeddings.request_handler'
```
