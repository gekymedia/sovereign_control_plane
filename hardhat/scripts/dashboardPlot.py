import json
import matplotlib.pyplot as plt
import os

# Load scenario report
report_path = os.path.join('storage', 'app', 'scenarioReport.json')
if not os.path.exists(report_path):
    print('Scenario report not found. Nothing to plot.')
    exit(0)

with open(report_path, 'r') as f:
    report = json.load(f)

# --- Overlay Tier Distribution ---
tiers = [int(r['overlayTier']) for r in report]
plt.figure(figsize=(6, 6))
plt.hist(tiers, bins=[0, 1, 2, 3], align='left', rwidth=0.6)
plt.xticks([0, 1, 2])
plt.xlabel('Overlay Tier')
plt.ylabel('Count')
plt.title('Overlay Tier Distribution Across Scenarios')
plt.savefig(os.path.join('storage', 'app', 'overlaytierdistribution.png'))
plt.close()

# --- Asset Success/Failure Rates ---
asset_outcomes = {}
for r in report:
    for a in r['assets']:
        asset = a['asset']
        status = a['status']
        asset_outcomes.setdefault(asset, {'executed': 0, 'failed': 0, 'dry-run': 0})
        if 'executed' in status:
            asset_outcomes[asset]['executed'] += 1
        elif 'failed' in status:
            asset_outcomes[asset]['failed'] += 1
        else:
            asset_outcomes[asset]['dry-run'] += 1

labels = list(asset_outcomes.keys())
executed = [asset_outcomes[a]['executed'] for a in labels]
failed = [asset_outcomes[a]['failed'] for a in labels]
dryrun = [asset_outcomes[a]['dry-run'] for a in labels]

x = range(len(labels))
plt.figure(figsize=(8, 6))
plt.bar(x, executed, label='Executed', color='green')
plt.bar(x, failed, bottom=executed, label='Failed', color='red')
plt.bar(x, dryrun, bottom=[executed[i] + failed[i] for i in x], label='Dry-run', color='blue')
plt.xticks(x, labels)
plt.ylabel('Count')
plt.title('Asset Outcomes Across Scenarios')
plt.legend()
plt.savefig(os.path.join('storage', 'app', 'asset_outcomes.png'))
plt.close()

# --- Governance Status Summary ---
gov_status = {'success': 0, 'failed': 0}
for r in report:
    if 'success' in r['governanceStatus']:
        gov_status['success'] += 1
    else:
        gov_status['failed'] += 1

plt.figure(figsize=(6, 6))
plt.pie(list(gov_status.values()), labels=list(gov_status.keys()), autopct='%1.1f%%', startangle=140)
plt.title('Governance Status Across Scenarios')
plt.savefig(os.path.join('storage', 'app', 'governance_status.png'))
plt.close()

print('✅ Dashboard charts generated: overlaytierdistribution.png, asset_outcomes.png, governance_status.png')