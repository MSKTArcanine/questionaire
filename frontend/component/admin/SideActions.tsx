import { SideAction } from "@/lib/types";
import Link from "next/link";

export default function SideActions(prop: Readonly<SideAction>){
    return (
        <Link
            className="btn btn-sm justify-start border-base-300 bg-base-100"
            href={prop.url}
        >
            {prop.name}
        </Link>
    )
}