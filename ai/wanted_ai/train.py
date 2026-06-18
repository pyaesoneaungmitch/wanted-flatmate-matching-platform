import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import OneHotEncoder, StandardScaler
from sklearn.pipeline import Pipeline
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import classification_report, roc_auc_score
import joblib

CSV_PATH = "v_ai_dataset.csv"  # rename your exported file to this
MODEL_OUT = "model.joblib"

df = pd.read_csv(CSV_PATH)

# Drop IDs; label is y_match
y = df["y_match"].astype(int)
X = df.drop(columns=["y_match", "user1_id", "user2_id"])

# Identify column types
cat_cols = [c for c in X.columns if c.endswith("_city") or c.endswith("_looking_for_type")]
num_cols = [c for c in X.columns if c.endswith("_budget_min") or c.endswith("_budget_max")]
bin_cols = [c for c in X.columns if c not in cat_cols + num_cols]

preprocess = ColumnTransformer(
    transformers=[
        ("cat", OneHotEncoder(handle_unknown="ignore"), cat_cols),
        ("num", StandardScaler(), num_cols),
        ("bin", "passthrough", bin_cols),
    ]
)

model = LogisticRegression(max_iter=2000, class_weight="balanced")

pipe = Pipeline(steps=[
    ("prep", preprocess),
    ("clf", model),
])

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.25, random_state=42, stratify=y
)

pipe.fit(X_train, y_train)

pred = pipe.predict(X_test)
proba = pipe.predict_proba(X_test)[:, 1]

print(classification_report(y_test, pred, digits=3))
print("ROC AUC:", roc_auc_score(y_test, proba))

joblib.dump({
    "pipeline": pipe,
    "feature_columns": list(X.columns),
}, MODEL_OUT)

print("Saved:", MODEL_OUT)