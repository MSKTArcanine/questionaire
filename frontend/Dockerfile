FROM node:alpine

WORKDIR /app

COPY package.json pnpm-lock.yaml* ./

RUN npm install -g pnpm@10.19.0 && pnpm install

COPY . .

RUN pnpm build

EXPOSE 3000

CMD [ "pnpm", "start"]