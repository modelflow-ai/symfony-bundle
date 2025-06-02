# Embeddings Request Handler With Multiple Generators

## Configuration

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: "%env(OPENAI_API_KEY)%"
        mistral:
            enabled: true
            credentials:
                api_key: "%env(MISTRAL_API_KEY)%"

    embeddings:
        generators:
            openai_ada:
                enabled: true
                provider: "openai"
                model: "text-embedding-ada-002"
            default:
                enabled: true
                provider: "mistral"
                model: "mistral-embed"
            openai_3_large:
                enabled: false
                provider: "openai"
                model: "text-embedding-3-large"
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
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    # OpenAI Ada Embedding Generator
    modelflow_ai.embeddings.openai_ada.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
        tags:
            - { name: modelflow_ai.embeddings.adapter, key: openai_ada }
    modelflow_ai.embeddings.openai_ada.splitter:
        class: ModelflowAi\Embeddings\Splitter\EmbeddingSplitter
    modelflow_ai.embeddings.openai_ada.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.openai_ada.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator
        tags:
            - { name: modelflow_ai.embeddings.generator, key: openai_ada }
        
    # Default Mistral Embedding Generator
    modelflow_ai.embeddings.default.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
        tags:
            - { name: modelflow_ai.embeddings.adapter, key: default }
    modelflow_ai.embeddings.default.splitter:
        class: ModelflowAi\Embeddings\Splitter\EmbeddingSplitter
    modelflow_ai.embeddings.default.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.default.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator
        tags:
            - { name: modelflow_ai.embeddings.generator, key: default }

    # Disabled Generator
    modelflow_ai.embeddings.openai_3_large.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
        tags:
            - { name: modelflow_ai.embeddings.adapter, key: openai_3_large }
    modelflow_ai.embeddings.openai_3_large.splitter:
        class: ModelflowAi\Embeddings\Splitter\EmbeddingSplitter
    modelflow_ai.embeddings.openai_3_large.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.openai_3_large.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator
        tags:
            - { name: modelflow_ai.embeddings.generator, key: openai_3_large }

    # Vector Stores
    modelflow_ai.embeddings.store.default:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
        tags:
            - { name: modelflow_ai.embeddings.store, key: default }

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
