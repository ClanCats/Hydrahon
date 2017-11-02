# Parameter parsing and escaping

One important thing to know is that Hydrahon will try to parse and auto escape your parameters. Read this carefully to avoid unexpected behavior.

> Warning: First of all, hydrahons escaping does **NOT** prevent SQL injection! Prepared statements will do that job for you. Hydrahon escapes keys & names to avoid a collision with a reserved keywords.
