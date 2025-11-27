export default function ListItemForm(prop: Readonly<PropListSearch>){
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
                    <button className="btn btn-sm btn-primary">
                        Ouvrir
                    </button>
                    <button className="btn btn-sm btn-error">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    )
}
