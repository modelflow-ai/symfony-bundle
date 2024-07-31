# Empty configuration

## Configuration

```yaml
modelflow_ai:
    providers:
        fireworksai:
            enabled: true
            credentials:
                api_key: 'api_key'

    adapters:
        fireworksai_llama3_1_405b:
            enabled: true
        fireworksai_llava_13b:
            enabled: true
        stable_diffusion_xl_fireworks:
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

    modelflow_ai.chat_adapter.fireworksai_llama3_1_405b.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.fireworksai_llama3_1_405b.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }

    modelflow_ai.completion_adapter.fireworksai_llama3_1_405b.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
    modelflow_ai.completion_adapter.fireworksai_llama3_1_405b.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }
        
    modelflow_ai.chat_adapter.fireworksai_llava_13b.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.fireworksai_llava_13b.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }

    modelflow_ai.completion_adapter.fireworksai_llava_13b.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
    modelflow_ai.completion_adapter.fireworksai_llava_13b.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }

    modelflow_ai.image_adapter.stable_diffusion_xl_fireworks.adapter:
        class: ModelflowAi\Image\Adapter\AIImageAdapterInterface
    modelflow_ai.image_adapter.stable_diffusion_xl_fireworks.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.image_request_handler.decision_tree.rule" }

    modelflow_ai.image_request_handler.decision_tree: ~
    modelflow_ai.image_request_handler: ~

aliases:
    ModelflowAi\DecisionTree\DecisionTreeInterface: modelflow_ai.chat_request_handler.decision_tree
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
