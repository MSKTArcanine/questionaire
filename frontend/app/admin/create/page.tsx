'use client';

export default function AdminCreateFormPage() {
  return (
    <div className="min-h-screen flex flex-col bg-base-200">
      <div className="flex flex-1 overflow-hidden">
        <main className="flex-1 flex flex-col overflow-y-auto no-scrollbar">
          <div className="border-b border-base-300 bg-base-100 px-8 py-4">
            <h2 className="text-2xl font-bold text-center">
              Création du formulaire
            </h2>
          </div>

          <div className="flex justify-center items-start p-8">
            <form
              className="card w-full max-w-xl bg-base-100 shadow-lg"
              onSubmit={(e) => {
                e.preventDefault();
              }}
            >
              <div className="card-body gap-6">
                <div className="form-control gap-2">
                  <p className="label pl-1">
                    <span className="label-text font-semibold">
                      Titre du formulaire
                    </span>
                  </p>
                  <div className="pl-4">
                    <input
                      type="text"
                      placeholder="Titre ici"
                      className="input input-bordered w-full"
                      name="title"
                      required
                    />
                  </div>
                </div>

                <div className="form-control gap-2">
                  <p className="label pl-1">
                    <span className="label-text font-semibold">
                      Description du formulaire
                    </span>
                  </p>
                  <div className="pl-4">
                    <input
                      type="text"
                      placeholder="Description ici"
                      className="input input-bordered w-full"
                      name="description"
                    />
                  </div>
                  <p className="label pl-4">
                    <span className="label-text-alt text-base-content/60">{/* WCAG */}
                      Optionnel, mais recommandé pour les usagers.
                    </span>
                  </p>
                </div>

                <div className="card-actions justify-center pt-2">
                  <button type="submit" className="btn btn-primary px-6">
                    Ajouter +
                  </button>
                </div>
              </div>
            </form>
          </div>
        </main>
      </div>
    </div>
  );
}
