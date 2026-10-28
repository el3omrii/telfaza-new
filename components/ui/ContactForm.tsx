"use client";

import type { FormEvent } from "react";
import { useState } from "react";

export default function ContactForm() {
  const [isSending, setIsSending] = useState(false);
  const [feedback, setFeedback] = useState<{
    type: "success" | "error";
    message: string;
  } | null>(null);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (isSending) return;

    const form = event.currentTarget;
    const formData = new FormData(form);
    setIsSending(true);
    setFeedback(null);

    try {
      const response = await fetch("/api/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email: formData.get("email"),
          subject: formData.get("subject"),
          body: formData.get("message"),
        }),
      });

      const result: unknown = await response.json().catch(() => null);
      if (!response.ok) {
        const errorMessage =
          typeof result === "object" &&
          result !== null &&
          "error" in result &&
          typeof result.error === "string"
            ? result.error
            : "We couldn't send your message. Please try again.";
        setFeedback({ type: "error", message: errorMessage });
        return;
      }

      form.reset();
      setFeedback({
        type: "success",
        message: "Thanks for reaching out. Your message has been sent.",
      });
    } catch {
      setFeedback({
        type: "error",
        message: "We couldn't send your message. Please try again.",
      });
    } finally {
      setIsSending(false);
    }
  }

  const fieldClassName =
    "mt-2 w-full rounded-xl border border-white/10 bg-[#0a0a0f]/70 px-4 py-3 text-sm text-white placeholder:text-gray-600 outline-none transition focus:border-[#e8490f]/60 focus:ring-2 focus:ring-[#e8490f]/15";

  return (
    <form
      className="rounded-2xl border border-white/[0.08] bg-white/[0.04] p-6 shadow-2xl shadow-black/20 sm:p-9"
      onSubmit={handleSubmit}
    >
      <div className="mb-8">
        <h2 className="text-2xl font-semibold text-white">Send us a message</h2>
        <p className="mt-2 text-sm text-gray-400">
          Fill in the details below and we&apos;ll get back to you as soon as
          we can.
        </p>
      </div>

      <div className="space-y-5">
        <div>
          <label
            className="text-sm font-medium text-gray-200"
            htmlFor="contact-email"
          >
            Your email
          </label>
          <input
            autoComplete="email"
            className={fieldClassName}
            id="contact-email"
            name="email"
            placeholder="you@example.com"
            required
            type="email"
          />
        </div>

        <div>
          <label
            className="text-sm font-medium text-gray-200"
            htmlFor="contact-subject"
          >
            Subject
          </label>
          <input
            className={fieldClassName}
            id="contact-subject"
            maxLength={150}
            name="subject"
            placeholder="What can we help with?"
            required
          />
        </div>

        <div>
          <label
            className="text-sm font-medium text-gray-200"
            htmlFor="contact-message"
          >
            Message
          </label>
          <textarea
            className={`${fieldClassName} min-h-36 resize-y`}
            id="contact-message"
            maxLength={5000}
            minLength={10}
            name="message"
            placeholder="Write your message here..."
            required
            rows={5}
          />
        </div>
      </div>

      <div className="mt-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div aria-live="polite" className="min-h-10 text-sm" role={feedback?.type === "error" ? "alert" : "status"}>
          {feedback && (
            <p className={feedback.type === "success" ? "text-emerald-400" : "text-red-400"}>
              {feedback.message}
            </p>
          )}
        </div>
        <button
          className="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#e8490f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#f15a22] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ff875f] disabled:cursor-not-allowed disabled:opacity-60"
          disabled={isSending}
          type="submit"
        >
          {isSending ? "SENDING..." : "SEND"}
        </button>
      </div>
    </form>
  );
}
