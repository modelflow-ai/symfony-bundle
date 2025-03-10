# Google Gemini Models Configuration

## Configuration

```yaml
modelflow_ai:
    providers:
        google_gemini:
            enabled: true
            credentials:
                api_key: "%env(GOOGLE_API_KEY)%"

    adapters:
        gemini_2_0_flash:
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
    modelflow_ai.chat_adapter.gemini_2_0_flash.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.gemini_2_0_flash.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
        
aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
