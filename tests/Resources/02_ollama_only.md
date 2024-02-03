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

    chat:
        adapters:
            - llama2
            - nexusraven
            - llava

    text:
        adapters:
            - llama2
            - nexusraven
            - llava
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
    modelflow_ai.chat_adapter.llama2.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.llama2.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.llava.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.llava.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.chat_adapter.nexusraven.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.chat_adapter.nexusraven.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.text_adapter.llama2.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.text_adapter.llama2.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.text_adapter.llava.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.text_adapter.llava.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
    modelflow_ai.text_adapter.nexusraven.adapter:
        class: ModelflowAi\Core\Model\AIModelAdapterInterface
    modelflow_ai.text_adapter.nexusraven.rule:
        class: ModelflowAi\Core\DecisionTree\DecisionRule
        tags:
            - { name: "modelflow_ai.decision_tree.rule" }
        
aliases:
    ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface: modelflow_ai.request_handler.decision_tree
    ModelflowAi\Core\AIRequestHandlerInterface: modelflow_ai.request_handler
```
