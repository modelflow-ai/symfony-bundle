# Experts Configuration

## Configuration

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: "%env(OPENAI_API_KEY)%"

    adapters:
        gpt4o:
            enabled: true

    experts:
        product_analyst:
            name: "Product Analyst"
            description: "Analyzes product data and provides recommendations"
            instructions: "You are a product analyst expert. Analyze the data and provide insights on market trends, user feedback, and recommendations for product improvement."
            response_format:
                type: "json_schema"
                schema:
                    type: "object"
                    properties:
                        market_trends:
                            type: "array"
                            items:
                                type: "string"
                        user_feedback_insights:
                            type: "array"
                            items:
                                type: "string"
                        recommendations:
                            type: "array"
                            items:
                                type: "string"
                        confidence_score:
                            type: "number"
                            minimum: 0
                            maximum: 100
        data_scientist:
            name: "Data Scientist"
            description: "Analyzes and interprets complex data sets"
            instructions: "You are a data scientist expert. Analyze the provided data sets and create visualizations, statistical models, and insights."
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
            
    # Expert Configuration
    modelflow_ai.experts.product_analyst.response_format:
        class: ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat
    modelflow_ai.experts.product_analyst:
        class: ModelflowAi\Experts\Expert
        arguments:
            - "Product Analyst"
            - "Analyzes product data and provides recommendations"
            - "You are a product analyst expert. Analyze the data and provide insights on market trends, user feedback, and recommendations for product improvement."
            - []
            - '@modelflow_ai.experts.product_analyst.response_format'
            
    modelflow_ai.experts.data_scientist:
        class: ModelflowAi\Experts\Expert
        arguments:
            - "Data Scientist"
            - "Analyzes and interprets complex data sets"
            - "You are a data scientist expert. Analyze the provided data sets and create visualizations, statistical models, and insights."
            - []
            - null
        
aliases:
    ModelflowAi\Chat\AIChatRequestHandlerInterface: modelflow_ai.chat_request_handler
```
