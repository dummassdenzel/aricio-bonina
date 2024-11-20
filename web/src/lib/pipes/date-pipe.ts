export function formatDate(
  dateString: string | null,
  format: "long" | "short" | "full" = "long"
): string {
  if (!dateString) return "";

  const date = new Date(dateString);

  const formats = {
    long: {
      year: "numeric",
      month: "long",
      day: "numeric",
    },
    short: {
      year: "numeric",
      month: "short",
      day: "numeric",
    },
    full: {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    },
  } as const;

  return date.toLocaleDateString("en-US", formats[format]);
}
