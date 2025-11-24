FROM node:alpine

WORKDIR /app

COPY package.json pnpm-lock.yaml* ./

RUN corepack enable && pnpm install

COPY . .

EXPOSE 3000

CMD [ "pnpm", "run", "dev", "--", "--hostname", "0.0.0.0" ]