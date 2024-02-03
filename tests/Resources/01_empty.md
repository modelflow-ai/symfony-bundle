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
    modelflow_ai.request_handler: ~
    modelflow_ai.request_handler.decision_tree: ~
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }
aliases:
    ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface: modelflow_ai.request_handler.decision_tree
    ModelflowAi\Core\AIRequestHandlerInterface: modelflow_ai.request_handler
```
