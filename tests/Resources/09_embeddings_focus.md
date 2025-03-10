# Embeddings Configuration

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
            openai_embeddings:
                enabled: true
                provider: "openai"
                model: "text-embedding-ada-002"
                splitter:
                    max_length: 1500
                    separator: " "
                cache:
                    enabled: true
                    cache_pool: cache.app
            mistral_embeddings:
                enabled: true
                provider: "mistral"
                model: "mistral-embed"
                splitter:
                    type: "service"
                    service_id: "app.custom_splitter"
        stores:
            filesystem: 'file://%kernel.project_dir%/var/embeddings'
            memory: 'memory://'
            qdrant:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333'
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }

    # OpenAI Embedding Generator
    modelflow_ai.embeddings.openai_embeddings.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
    modelflow_ai.embeddings.openai_embeddings.adapter.cache:
        class: ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter
    modelflow_ai.embeddings.openai_embeddings.splitter:
        class: ModelflowAi\Embeddings\Splitter\EmbeddingSplitter
    modelflow_ai.embeddings.openai_embeddings.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.openai_embeddings.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator

    # Mistral Embedding Generator
    modelflow_ai.embeddings.mistral_embeddings.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
    modelflow_ai.embeddings.mistral_embeddings.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.mistral_embeddings.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator

    # Vector Stores
    modelflow_ai.embeddings.store.filesystem:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
    modelflow_ai.embeddings.store.memory:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
    modelflow_ai.embeddings.store.qdrant:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface

    # Embedding Store Factory
    modelflow_ai.embeddings_store_factory: ~

aliases:
    modelflow_ai.embeddings.mistral_embeddings.splitter: 'app.custom_splitter'
```
