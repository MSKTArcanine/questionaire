import { NextRequest, NextResponse } from "next/server";

const BACKEND_URL = process.env.BACKEND_URL;

export async function POST(req: NextRequest) {
  const { email, password } = await req.json();

  if (!email || !password) {
    return NextResponse.json(
      { error: "Email et PIN requis." },
      { status: 400 }
    );
  }

  // api/auth/user pour VISITEUR
  const symfonyRes = await fetch(`${BACKEND_URL}/api/auth/user`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email, password }),
  });

  if (!symfonyRes.ok) {
    const data = await symfonyRes.json().catch(() => null);
    const message =
      data?.message ||
      data?.error ||
      "PIN invalide ou erreur serveur.";
    return NextResponse.json({ error: message }, { status: 401 });
  }

  const data = await symfonyRes.json();
  const token = data.token;

  if (!token) {
    return NextResponse.json(
      { error: "pas de token." },
      { status: 500 }
    );
  }

  // Pareil
  const res = NextResponse.json({ ok: true }, { status: 200 });

  res.cookies.set("jwt", token, {
    httpOnly: true,
    secure: true,
    sameSite: "lax",
    path: "/",
    maxAge: 60 * 60,
  });

  return res;
}
