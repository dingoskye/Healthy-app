import OpenAI from "openai";

const client = new OpenAI({
    apiKey: "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" // ⚠️ Replace with your real key
});

const response = await client.responses.create({
    model: "gpt-5",
    input: "Give me a one-sentence explanation to star wars",
});

console.log(response.output_text);