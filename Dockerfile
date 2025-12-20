# Stage 1: Build
FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src

# Copier le fichier csproj et restaurer les dépendances
COPY bresil_burger_csharp/*.csproj bresil_burger_csharp/
RUN dotnet restore bresil_burger_csharp/bresil_burger_csharp.csproj

# Copier tout le reste et builder
COPY . .
WORKDIR /src/bresil_burger_csharp
RUN dotnet build bresil_burger_csharp.csproj -c Release -o /app/build

# Stage 2: Publish
FROM build AS publish
RUN dotnet publish bresil_burger_csharp.csproj -c Release -o /app/publish /p:UseAppHost=false

# Stage 3: Runtime
FROM mcr.microsoft.com/dotnet/aspnet:8.0 AS final
WORKDIR /app
EXPOSE 10000
COPY --from=publish /app/publish .

# Configurer l'application pour écouter sur le port fourni par Render
ENV PORT=10000
ENV ASPNETCORE_URLS=http://+:10000

ENTRYPOINT ["dotnet", "bresil_burger_csharp.dll"]
