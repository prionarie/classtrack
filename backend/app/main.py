from fastapi import FastAPI
from sqlalchemy import text
from sqlalchemy import create_engine
import os

app = FastAPI(title="ClassTrack API")

engine = create_engine(os.environ["DATABASE_URL"], pool_pre_ping=True)

@app.get("/api/health")
def health():
    with engine.connect() as conn:
        conn.execute(text("SELECT 1"))
    return {"status": "ok"}