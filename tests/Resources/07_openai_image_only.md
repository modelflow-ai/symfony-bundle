# OpenAI Image Generation Only

## Configuration

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: "%env(OPENAI_API_KEY)%"

    adapters:
        dall_e_3:
            enabled: true
        dall_e_2:
            enabled: true
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }
    modelflow_ai.image_adapter.dall_e_3.adapter:
        class: ModelflowAi\Image\Adapter\AIImageAdapterInterface
    modelflow_ai.image_adapter.dall_e_3.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.image_request_handler.decision_tree.rule" }
    modelflow_ai.image_adapter.dall_e_2.adapter:
        class: ModelflowAi\Image\Adapter\AIImageAdapterInterface
    modelflow_ai.image_adapter.dall_e_2.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.image_request_handler.decision_tree.rule" }
    modelflow_ai.image_request_handler.decision_tree: ~
    modelflow_ai.image_request_handler: ~
        
aliases: 
    ModelflowAi\Image\AIImageRequestHandlerInterface: modelflow_ai.image_request_handler
```
