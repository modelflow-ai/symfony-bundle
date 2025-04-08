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
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.chat.tool_executor:
        class: ModelflowAi\Chat\ToolInfo\ToolExecutor
    modelflow_ai.chat.middleware.tool_execution:
        class: ModelflowAi\Chat\Middleware\Tools\ToolExecutionMiddleware
        tags:
            - { name: "modelflow_ai.chat.middleware" }
    modelflow_ai.chat_request_handler: ~
    modelflow_ai.chat_request_handler.decision_tree: ~
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }
    modelflow_ai.chat_adapter.gpt3.5.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.gpt3.5.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.gpt4.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.gpt4.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.llama2.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.llama2.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.llava.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.llava.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_medium.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.mistral_medium.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_small.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.mistral_small.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_tiny.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.mistral_tiny.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.nexusraven.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.nexusraven.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }

    modelflow_ai.completion_adapter.llama2.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
    modelflow_ai.completion_adapter.llama2.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }
    modelflow_ai.completion_adapter.llava.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
    modelflow_ai.completion_adapter.llava.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }
    modelflow_ai.completion_adapter.nexusraven.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
    modelflow_ai.completion_adapter.nexusraven.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }
        
aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
