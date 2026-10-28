import { NextResponse } from "next/server";

const API = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";
const CONTACT_VALIDATION_SECRET = process.env.CONTACT_VALIDATION_SECRET;

type ContactPayload = {
  email: string;
  subject: string;
  body: string;
};

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null;
}

function readContactPayload(value: unknown): ContactPayload | null {
  if (!isRecord(value)) return null;

  const { email, subject, body } = value;
  if (
    typeof email !== "string" ||
    typeof subject !== "string" ||
    typeof body !== "string"
  ) {
    return null;
  }

  const normalized = {
    email: email.trim(),
    subject: subject.trim(),
    body: body.trim(),
  };

  if (
    normalized.email.length > 254 ||
    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized.email) ||
    normalized.subject.length === 0 ||
    normalized.subject.length > 150 ||
    /[\r\n]/.test(normalized.subject) ||
    normalized.body.length < 10 ||
    normalized.body.length > 5000
  ) {
    return null;
  }

  return normalized;
}

export async function POST(request: Request) {
  let input: unknown;
  try {
    input = await request.json();
  } catch {
    return NextResponse.json({ error: "Invalid request body." }, { status: 400 });
  }

  const payload = readContactPayload(input);
  if (!payload) {
    return NextResponse.json(
      { error: "Please provide a valid email, subject, and message." },
      { status: 400 },
    );
  }

  if (!CONTACT_VALIDATION_SECRET) {
    console.error("Contact form is unavailable: CONTACT_VALIDATION_SECRET is not configured.");
    return NextResponse.json(
      { error: "The contact form is temporarily unavailable. Please try again later." },
      { status: 503 },
    );
  }

  try {
    const response = await fetch(`${API.replace(/\/+$/, "")}/sendmail`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-Validation-Secret": CONTACT_VALIDATION_SECRET,
      },
      body: JSON.stringify(payload),
      signal: AbortSignal.timeout(15_000),
      cache: "no-store",
    });

    if (!response.ok) {
      console.error(`Contact mail backend returned HTTP ${response.status}.`);
      return NextResponse.json(
        { error: "We couldn't send your message. Please try again later." },
        { status: 502 },
      );
    }

    return NextResponse.json({ ok: true });
  } catch {
    console.error("Contact mail backend request failed.");
    return NextResponse.json(
      { error: "We couldn't send your message. Please try again later." },
      { status: 502 },
    );
  }
}
