# Empty configuration

## Configuration

```yaml
modelflow_ai:
    providers:
        ollama:
            enabled: true
            url: "%env(OLLAMA_URL)%"

    adapters:
        llama2:
            enabled: true
        nexusraven:
            enabled: true
        llava:
            enabled: true
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.chat_request_handler: ~
    modelflow_ai.chat_request_handler.decision_tree: ~
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }
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
    ModelflowAi\DecisionTree\DecisionTreeInterface: modelflow_ai.chat_request_handler.decision_tree
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
