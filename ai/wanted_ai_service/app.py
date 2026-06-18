from flask import Flask, request, jsonify
import joblib
import pandas as pd
import numpy as np

MODEL_PATH = "model.joblib"

bundle = joblib.load(MODEL_PATH)
pipe = bundle["pipeline"]
feature_cols = bundle["feature_columns"]

app = Flask(__name__)

@app.post("/predict")
def predict():
    payload = request.get_json(force=True) or {}

    row = {}
    for c in feature_cols:
        v = payload.get(c, None)

        # If missing, fill defaults
        if v is None:
            # heuristic: budgets are numeric, booleans are numeric, city/type are strings
            if c.endswith("_budget_min") or c.endswith("_budget_max"):
                v = 0
            elif c.endswith("_city") or c.endswith("_looking_for_type"):
                v = ""
            else:
                # hobbies/prefs booleans
                v = 0

        row[c] = v

    X = pd.DataFrame([row], columns=feature_cols)

    # Ensure no NaNs remain
    X = X.fillna(0)

    p = float(pipe.predict_proba(X)[0, 1])
    threshold = float(payload.get("_threshold", 0.5))
    y = 1 if p >= threshold else 0

    return jsonify({"ok": True, "p_match": p, "pred_match": y})

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5005, debug=True)