# Anthropic Claude Models Only

## Configuration

```yaml
modelflow_ai:
    providers:
        anthropic:
            enabled: true
            credentials:
                api_key: "%env(ANTHROPIC_API_KEY)%"
            max_tokens: 2048

    adapters:
        claude_3_5_sonnet:
            enabled: true
        claude_3_opus:
            enabled: true
        claude_3_sonnet:
            enabled: true
        claude_3_5_haiku:
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
    modelflow_ai.chat_adapter.claude_3_5_sonnet.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.claude_3_5_sonnet.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.claude_3_opus.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.claude_3_opus.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.claude_3_sonnet.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.claude_3_sonnet.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.claude_3_5_haiku.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.claude_3_5_haiku.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
        
aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
