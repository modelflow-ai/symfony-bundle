# Custom Adapters Configuration

## Configuration

```yaml
modelflow_ai:
    providers:
        custom:
            my_custom_provider:
                chat_factory: "app.my_custom_chat_factory"
                completion_factory: "app.my_custom_completion_factory"
                image_factory: "app.my_custom_image_factory"
                embeddings_factory: "app.my_custom_embeddings_factory"

    adapters:
        my_custom_model:
            enabled: true
            provider: "my_custom_provider"
            model: "custom-model-1"
            chat: true
            completion: true
            stream: true
            tools: true
            image_to_text: true
            text_to_image: true
            priority: 200
```

## Expects

```yaml
bundles:
    - ModelflowAi\Integration\Symfony\ModelflowAiBundle

services:
    modelflow_ai.chat_request_handler: ~
    modelflow_ai.chat_request_handler.decision_tree: ~
    modelflow_ai.completion_request_handler: ~
    modelflow_ai.completion_request_handler.decision_tree: ~
    modelflow_ai.image_request_handler: ~
    modelflow_ai.image_request_handler.decision_tree: ~
    modelflow_ai.command.chat:
        tags:
            - { name: console.command, command: modelflow-ai:chat }
            
    # Custom Chat Adapter
    modelflow_ai.chat_adapter.my_custom_model.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
        factory: ['@app.my_custom_chat_factory', 'createChatAdapter']
    modelflow_ai.chat_adapter.my_custom_model.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
            
    # Custom Completion Adapter
    modelflow_ai.completion_adapter.my_custom_model.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
        factory: ['@app.my_custom_completion_factory', 'createCompletionAdapter']
    modelflow_ai.completion_adapter.my_custom_model.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }
            
    # Custom Image Adapter
    modelflow_ai.image_adapter.my_custom_model.adapter:
        class: ModelflowAi\Image\Adapter\AIImageAdapterInterface
        factory: ['@app.my_custom_image_factory', 'createImageAdapter']
    modelflow_ai.image_adapter.my_custom_model.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.image_request_handler.decision_tree.rule" }

aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
    ModelflowAi\Completion\AICompletionRequestHandlerInterface: modelflow_ai.completion_request_handler
    ModelflowAi\Image\AIImageRequestHandlerInterface: modelflow_ai.image_request_handler
```
