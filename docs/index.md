## Documentation

Marble provides some of the great features of ORM frameworks — entity manager, unit of work,
identity map, repository factory, query caching — without the object-relational mapping itself.
It’s up to you to implement the actual reading and writing of entity data from and to whatever
persistence layer you’re using.

- **Unit of work:** Bundles all your database operations into a single transaction-like flush, ensuring data consistency.
- **Identity map:** Ensures that each unique entity is instantiated only once per session, preventing conflicting object states.
- **Automatic change tracking:** Automatically detects modifications to your entities, so you only save what has actually changed.
- **Ordered flushes:** Intelligently calculates the correct order to persist entities based on their associations, handling dependencies for you.
- **Persistence-agnostic:** Since you implement the readers and writers, you can use any storage engine — SQL, NoSQL, or even external APIs — while keeping your domain logic clean.

### Contents

1. [Installation](installation.md)
2. [Usage](usage.md)
3. [What this bundle does](technical.md)
