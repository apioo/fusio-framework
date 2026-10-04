---
name: fusio-sdk
description: Generate a type-safe client SDK (TypeScript, PHP, Python, Java, Go, C#) for this Fusio API. Use when the user wants a client library to consume the API.
---

# Generate a client SDK

```
php bin/fusio generate:sdk <type>
```

| Type | Language |
|------|----------|
| `client-typescript` | TypeScript |
| `client-php` | PHP |
| `client-python` | Python |
| `client-java` | Java |
| `client-go` | Go |
| `client-csharp` | C# |

Options:
- `-o, --output=DIR`: target directory (default `output/`)
- `-s, --namespace=NS`: namespace or package name for the generated code
- `-e, --filter=FILTER`: only include matching operations
- `-r, --raw`: write the plain files instead of a zip

The SDK is generated from the **deployed** operations and schemas. Run `php bin/fusio deploy` first if `resources/`
has changed. Ask which language the user needs if they don't say. Afterwards, tell them where the zip or files were
written.
