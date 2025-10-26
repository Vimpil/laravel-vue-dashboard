# Используем официальный Node.js образ
FROM node:16

# Рабочая директория внутри контейнера
WORKDIR /app

# Копируем package.json и package-lock.json
COPY package*.json ./

# Устанавливаем зависимости (включая crypto-js, если он в package.json)
RUN npm install

# Копируем остальной код
COPY . .

# Устанавливаем переменную окружения для Vue dev-server
ENV HOST 0.0.0.0
ENV PORT 8081

# Пробрасываем порт dev-сервера
EXPOSE 8081

# Команда запуска dev-сервера Vue
CMD ["npm", "run", "serve", "--", "--port", "8081", "--host", "0.0.0.0"]
