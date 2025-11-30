import type { NextRequest } from "next/server";
import { NextResponse } from "next/server";

const BACKEND_URL = process.env.BACKEND_URL ?? "http://backend:8000";

export async function middleware(req: NextRequest) {
  const { pathname, search } = req.nextUrl;

  //Admin uniquement.
  if (!pathname.startsWith("/admin")) {
    return NextResponse.next();
  }

  const token = req.cookies.get("jwt")?.value ?? null;

  // Si jamais pas de JWT, on prépare la route
  const loginUrl = new URL("/login/admin", req.url);
  loginUrl.searchParams.set("redirect", pathname + search);

  // 1) Pas de JWT => on envoie à la page de login admin
  if (!token) {
    return NextResponse.redirect(loginUrl);
  }

  // 2) JWT ? => Admin ?
  const resUser = await fetch(`${BACKEND_URL}/api/auth/me`, {
    method: "GET",
    headers: {
      Authorization: `Bearer ${token}`,
    },
    cache: "no-store",
  });

  // Pas co ? GO to login.
  if (resUser.status === 401) {
    return NextResponse.redirect(loginUrl);
  }

  // ROLE_USER ? Retournes à tes questionnaires. Cordialement.
  if (resUser.status === 403) {
    const userUrl = new URL("/questionnaires", req.url);
    return NextResponse.redirect(userUrl);
  }

  // 500 => On redirect quand même.
  if (!resUser.ok) {
    return NextResponse.redirect(loginUrl);
  }

  // 100% c'est un admin.
  return NextResponse.next();
}

// NextJS : routes /admin/*
export const config = {
  matcher: ["/admin/:path*"],
};
