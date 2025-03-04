
# Empty configuration

## Configuration

```yaml
modelflow_ai: 
    embeddings:
        stores:
            filesystem: 'file://%kernel.project_dir%/var/embeddings'
            memory: 'memory://'
            qdrant: 'qdrant://127.0.0.1:6333'
```

## Expects

```yaml
services:
    modelflow_ai.embeddings_store_factory:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreFactory
                
    modelflow_ai.embeddings_stores.filesystem_factory:
        class: ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStoreFactory
        tags:
            - { name: modelflow_ai.embeddings_store_factory }
        
    modelflow_ai.embeddings_stores.memory_factory:
        class: ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStoreFactory
        tags:
            - { name: modelflow_ai.embeddings_store_factory }

    modelflow_ai.embeddings_stores.qdrant_factory:
        class: ModelflowAi\Embeddings\Store\Qdrant\QdrantEmbeddingsStoreFactory
        tags:
            - { name: modelflow_ai.embeddings_store_factory }
    
    modelflow_ai.embeddings.store.filesystem:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
    
    modelflow_ai.embeddings.store.memory:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface

    modelflow_ai.embeddings.store.qdrant:
        class: ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface
```
