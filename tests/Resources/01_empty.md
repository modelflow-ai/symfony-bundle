# Empty configuration

## Configuration

```yaml
modelflow_ai: []
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
aliases:
    ModelflowAi\DecisionTree\DecisionTreeInterface: modelflow_ai.chat_request_handler.decision_tree
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
