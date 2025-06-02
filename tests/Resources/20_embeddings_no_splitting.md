# Embeddings Configuration with No Splitting

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
            openai_embeddings:
                enabled: true
                provider: "openai"
                model: "text-embedding-ada-002"
                splitter:
                    type: "none"
        stores:
            memory: 'memory://'
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }

    # OpenAI Embedding Generator with NoOp Splitter
    modelflow_ai.embeddings.openai_embeddings.adapter:
        class: ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface
        tags:
            - { name: modelflow_ai.embeddings.adapter, key: openai_embeddings }
    modelflow_ai.embeddings.openai_embeddings.splitter:
        class: ModelflowAi\Embeddings\Splitter\NoOpEmbeddingSplitter
    modelflow_ai.embeddings.openai_embeddings.formatter:
        class: ModelflowAi\Embeddings\Formatter\EmbeddingFormatter
    modelflow_ai.embeddings.openai_embeddings.generator:
        class: ModelflowAi\Embeddings\Generator\EmbeddingGenerator
        tags:
            - { name: modelflow_ai.embeddings.generator, key: openai_embeddings }

    # Vector Store
    modelflow_ai.embeddings.store.memory:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
        tags:
            - { name: modelflow_ai.embeddings.store, key: memory }

    # Embedding Store Factory
    modelflow_ai.embeddings_store_factory: ~
```