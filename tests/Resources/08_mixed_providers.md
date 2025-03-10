# Mixed Providers with Custom Priority

## Configuration

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: "%env(OPENAI_API_KEY)%"
        anthropic:
            enabled: true
            credentials:
                api_key: "%env(ANTHROPIC_API_KEY)%"
        mistral:
            enabled: true
            credentials:
                api_key: "%env(MISTRAL_API_KEY)%"

    adapters:
        gpt4o:
            enabled: true
            priority: 100 # Higher priority
        claude_3_opus:
            enabled: true
            priority: 50 # Medium priority
        mistral_large:
            enabled: true
            priority: 10 # Lower priority
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
    modelflow_ai.chat_adapter.gpt4o.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.gpt4o.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.claude_3_opus.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.claude_3_opus.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.chat_adapter.mistral_large.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.mistral_large.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
        
aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
