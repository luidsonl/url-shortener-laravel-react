```mermaid
erDiagram
    USER ||--o{ SUBSCRIPTION : has
    PLAN ||--o{ SUBSCRIPTION : defines
    SUBSCRIPTION ||--o{ SUBSCRIPTION_EVENT : generates
    SUBSCRIPTION_EVENT ||--o| TRANSACTION : has
    
    USER {
        int id PK
        string name
        string email
        string role
    }
    
    PLAN {
        int id PK
        string name
        int price_monthly
        int price_yearly
        string description
        bool is_active
    }
    
    SUBSCRIPTION {
        int id PK
        int user_id FK
        int plan_id FK
        datetime expires_at
        string status
    }
    
    SUBSCRIPTION_EVENT {
        int id PK
        int subscription_id FK
        string event_type
    }

    TRANSACTION {
        int id PK
        int subscription_event_id FK
        datetime processed_at
        int amount
        string currency
    }
```