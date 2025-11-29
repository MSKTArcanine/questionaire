export default function FormFillFooter() {
  return (
    <footer className="w-full border-t border-base-300 bg-base-100 px-8 py-4">
      <div className="flex gap-4 text-sm text-base-content/70">
        <button className="btn btn-ghost btn-xs rounded-none">
          Mentions légales
        </button>
        <button className="btn btn-ghost btn-xs rounded-none">
          Contact
        </button>
        <button className="btn btn-ghost btn-xs rounded-none">
          Je sais pas...
        </button>
      </div>
    </footer>
  );
}