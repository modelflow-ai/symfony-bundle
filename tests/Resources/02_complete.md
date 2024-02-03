# Empty configuration

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
        ollama:
            enabled: true
            url: "%env(OLLAMA_URL)%"

    adapters:
        gpt4:
            enabled: true
        gpt3.5:
            enabled: true
        mistral_tiny:
            enabled: true
        mistral_small:
            enabled: true
        mistral_medium:
            enabled: true
        llama2:
            enabled: true
        nexusraven:
            enabled: true
        llava:
            enabled: true

    embeddings:
        generators:
            app.openai_embeddings_generator:
                enabled: true
                provider: "openai"
                model: "text-embedding-ada-002"
                splitter:
                    max_length: 1000
                    separator: " "
                cache:
                    enabled: true
                    cache_pool: cache.app

    chat:
        adapters:
            - gpt4
            - gpt3.5
            - mistral_tiny
            - mistral_small
            - mistral_medium
            - llama2
            - nexusraven
            - llava

    text:
        adapters:
            - llama2
            - nexusraven
            - llava
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.request_handler: ~
    modelflow_ai.request_handler.decision_tree: ~
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }
    modelflow_ai.chat_adapter.gpt3.5.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.gpt3.5.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.gpt4.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.gpt4.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.llama2.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.llama2.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.llava.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.llava.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_medium.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.mistral_medium.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_small.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.mistral_small.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_tiny.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.mistral_tiny.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.nexusraven.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.nexusraven.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.text_adapter.llama2.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.text_adapter.llama2.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.text_adapter.llava.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.text_adapter.llava.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.text_adapter.nexusraven.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.text_adapter.nexusraven.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
        
aliases:
    ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface: modelflow_ai.request_handler.decision_tree
    ModelflowAi\Core\AIRequestHandlerInterface: modelflow_ai.request_handler
```
