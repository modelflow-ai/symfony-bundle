# Multimodal Models Configuration

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
        ollama:
            enabled: true
            url: "%env(OLLAMA_URL)%"

    adapters:
        gpt4o:
            enabled: true
        claude_3_opus:
            enabled: true
        pixtral_large:
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
            
    # OpenAI GPT-4o
    modelflow_ai.chat_adapter.gpt4o.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.gpt4o.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
            
    # Anthropic Claude 3 Opus
    modelflow_ai.chat_adapter.claude_3_opus.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.claude_3_opus.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
            
    # Mistral Pixtral Large
    modelflow_ai.chat_adapter.pixtral_large.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.pixtral_large.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
            
    # Ollama LLaVA
    modelflow_ai.chat_adapter.llava.adapter:
        class: ModelflowAi\Chat\Adapter\AIChatAdapterInterface
    modelflow_ai.chat_adapter.llava.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.chat_request_handler.decision_tree.rule" }
    modelflow_ai.completion_adapter.llava.adapter:
        class: ModelflowAi\Completion\Adapter\AICompletionAdapterInterface
    modelflow_ai.completion_adapter.llava.rule:
        class: ModelflowAi\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.completion_request_handler.decision_tree.rule" }
        
aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
