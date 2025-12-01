import SideActions from "@/component/admin/SideActions";
import type { ReactNode } from "react";

export default function AdminLayout({ children }: { readonly children: ReactNode }) {
  return (
    <div className="min-h-screen flex flex-col bg-base-200">
      <header className="navbar bg-base-100 border-b border-base-300 px-6 shrink-0">
        <div className="flex-1">
          <span className="text-lg font-semibold">Outils administratifs</span>
        </div>
      </header>

      <div className="flex flex-1">
        <aside className="w-64 bg-base-100 border-r border-base-300 p-4 flex flex-col gap-3 shrink-0">
          <div className="border border-base-300 rounded-lg py-3 text-center font-semibold">
            Actions
          </div>
          <SideActions name="Outil de création" url="/admin/create"/>
          <SideActions name="Outil de recherche" url="/admin/search"/>
          <SideActions name="Dashboard" url="/admin/dashboard"/>
          <button className="btn btn-sm mt-auto justify-start border-base-300 bg-base-100">
            Autre outil
          </button>
        </aside>

        <main className="flex-1 flex flex-col">
          {children}
        </main>
      </div>

      <footer className="bg-neutral text-neutral-content border-t border-neutral-700 shrink-0">
        <div className="max-w-6xl mx-auto flex flex-wrap justify-between gap-x-12 gap-y-8 px-10 py-6 text-sm">
          <div className="space-y-2 min-w-[180px]">
            <h6 className="font-semibold uppercase text-xs tracking-wide text-neutral-400">
              À propos de la médiathèque
            </h6>
            <p className="hover:underline cursor-pointer">Présentation</p>
            <p className="hover:underline cursor-pointer">Équipe</p>
            <p className="hover:underline cursor-pointer">Horaires</p>
            <p className="hover:underline cursor-pointer">Accès &amp; transports</p>
          </div>

          <div className="space-y-2 min-w-[220px]">
            <h6 className="font-semibold uppercase text-xs tracking-wide text-neutral-400">
              Contact
            </h6>
            <p>Médiathèque municipale de Demo-ville</p>
            <p>12, rue des Lecteurs</p>
            <p>75000 Demo-ville</p>
            <p className="mt-2">
              Tél. : 01 23 45 67 89
              <br />
              Email : contact@mediatheque.fr
            </p>
          </div>

          <div className="space-y-2 min-w-[200px]">
            <h6 className="font-semibold uppercase text-xs tracking-wide text-neutral-400">
              Services en ligne
            </h6>
            <p className="hover:underline cursor-pointer">Catalogue en ligne</p>
            <p className="hover:underline cursor-pointer">Réservation de documents</p>
            <p className="hover:underline cursor-pointer">Ressources numériques</p>
            <p className="hover:underline cursor-pointer">Agenda des événements</p>
          </div>

          <div className="space-y-2 min-w-[200px]">
            <h6 className="font-semibold uppercase text-xs tracking-wide text-neutral-400">
              Informations légales
            </h6>
            <p className="hover:underline cursor-pointer">Mentions légales</p>
            <p className="hover:underline cursor-pointer">Politique de confidentialité</p>
            <p className="hover:underline cursor-pointer">Accessibilité</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
