# Embeddings Request Handler With Multiple Stores

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
            documents_store:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/documents'
            products_store:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/products'
            users_store:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/users'
            default:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/default'
        request_handler:
            enabled: true
            mapping:
                'App\Entity\Document':
                    key: 'documents_store'
                'App\Entity\Product':
                    key: 'products_store'
                'App\Entity\User':
                    key: 'users_store'
                'App\Entity\Generic':
                    key: 'default'
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
    modelflow_ai.embeddings.store.documents_store:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
    modelflow_ai.embeddings.store.products_store:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
    modelflow_ai.embeddings.store.users_store:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
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
