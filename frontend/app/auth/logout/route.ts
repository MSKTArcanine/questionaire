import { NextResponse } from "next/server";

export async function GET() {
    // JWTus deletus GETUS
    const res = NextResponse.json({ ok: true }, { status: 200 });
    
    res.cookies.set("jwt", "", {
        httpOnly: true,
        secure: true,
        sameSite: "lax",
        path: "/",
        maxAge: 0
    });
    
    return res;
}
