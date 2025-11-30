import { PropUserListSearch } from "@/lib/types";
import Link from "next/link";

export default function UserListItemForm(prop: Readonly<PropUserListSearch>){
    return (
        <div
        className="card bg-base-100 border border-base-300 shadow-sm">
        <div className="card-body flex-row items-center justify-between gap-4">
        <div className="flex-1">
        <h3 className="font-semibold text-base">
        {prop.title}
        </h3>
        <p className="text-sm text-base-content/60">
        {prop.description}
        </p>
        </div>
        <div className="flex gap-2 shrink-0">
        <Link
        href={`/questionnaires/${prop.slug}`}
        className="btn btn-sm btn-primary"
        >
        GO
        </Link>
        </div>
        </div>
        </div>
    )
}
