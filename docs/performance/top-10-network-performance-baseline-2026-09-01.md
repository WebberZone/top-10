# Top 10 network performance baseline

Captured on 2026-09-01 against `https://wpnetwork.test` before and after the Issue #189 changes.

## Test environment

- WordPress 7.1, Top 10 Pro, Query Monitor 4.0.7.
- Database: `wpnetwork`, MySQL, multisite sites 1, 2 and 4 active.
- Each page was loaded in Chrome with ten sequential fresh reloads.
- Server time and database time are Query Monitor values. Query counts are per request; “Top 10 queries” means queries reported by the plugin component.

## Baseline before Issue #189

| Page | Server mean / median (s) | Server range (s) | DB mean (s) | DB queries | Top 10 queries | `SHOW TABLES` |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Site dashboard | 0.4224 / 0.3942 | 0.3611–0.6781 | 0.0288 | 811 | 26 | 4 |
| Network dashboard | 0.3860 / 0.3844 | 0.3731–0.4161 | 0.0925 | 757 | 23 | 4 |
| Network popular posts, total | 0.3120 / 0.3114 | 0.2968–0.3249 | 0.0644 | 196 | 128 | 4 |
| Network Tools | 0.2830 / 0.2729 | 0.2573–0.3238 | 0.0575 | 88 | 19 | 4 |

The representative Network Dashboard query returned 9 rows and took 0.034987 seconds for total ordering and 0.024522 seconds for daily ordering:

```sql
SELECT ttt.postnumber AS ID, ttt.cntaccess AS total_count,
       SUM(ttd.cntaccess) AS daily_count, ttt.blog_id AS blog_id
FROM wp_top_ten AS ttt
LEFT JOIN (
    SELECT *
    FROM wp_top_ten_daily AS ttd
    WHERE DATE(ttd.dp_date) >= DATE('2026-09-01')
      AND DATE(ttd.dp_date) <= DATE('2026-09-01')
) AS ttd
  ON ttt.postnumber = ttd.postnumber AND ttt.blog_id = ttd.blog_id
GROUP BY ttt.postnumber, ttt.blog_id
ORDER BY total_count DESC
LIMIT 0, 9;
```

Every baseline page also performed four uncached checks:

```sql
SHOW TABLES LIKE 'wp\\_top\\_ten';
SHOW TABLES LIKE 'wp\\_top\\_ten\\_daily';
SHOW TABLES LIKE 'wp\\_top\\_ten\\_visits\\_log';
SHOW TABLES LIKE 'wp\\_top\\_ten\\_visits\\_funnel';
```

## Implementation measured after Issue #189

Ten cold Network Dashboard runs were made after clearing the network widget cache before each request. Each returned the page successfully, with 765 total database queries, 30 Top 10 queries, and no old full-table join:

| Run | Server (s) | DB (s) |
| ---: | ---: | ---: |
| 1 | 0.325928 | 0.049440 |
| 2 | 0.320148 | 0.049923 |
| 3 | 0.321544 | 0.050019 |
| 4 | 0.314386 | 0.046072 |
| 5 | 0.326307 | 0.049735 |
| 6 | 0.320080 | 0.046712 |
| 7 | 0.322853 | 0.046731 |
| 8 | 0.318505 | 0.046479 |
| 9 | 0.319938 | 0.049549 |
| 10 | 0.319957 | 0.047657 |
| **Mean** | **0.320965** | **0.048232** |
| **Median** | **0.320114** | **0.048548** |

Compared with the baseline Network Dashboard, this is a 16.85% lower server time and a 47.86% lower database time. The warm cached dashboard request was 0.298044 seconds with 742 total queries, 6 Top 10 queries, and no popular-post query.

The optimized total-order path is:

```sql
SELECT postnumber AS ID, blog_id, cntaccess AS total_count
FROM wp_top_ten
ORDER BY cntaccess DESC, postnumber ASC, blog_id ASC
LIMIT 0, 9;
```

The optimized daily-order path pre-aggregates only the selected date range:

```sql
SELECT postnumber AS ID, blog_id, SUM(cntaccess) AS daily_count
FROM wp_top_ten_daily
WHERE dp_date >= '2026-09-01 00:00:00'
  AND dp_date < '2026-09-02 00:00:00'
GROUP BY postnumber, blog_id
ORDER BY daily_count DESC, postnumber ASC, blog_id ASC
LIMIT 0, 9;
```

The second phase fetches the other count for the selected `(postnumber, blog_id)` pairs only. It uses an OR-of-pairs predicate, not a row-constructor `IN` expression. Query plans used `idx_cntaccess` for total ordering (cost 30.7, 304 estimated rows) and an `idx_dp_date` range scan for daily ordering (cost 5.21, 11 estimated rows).

## Ten additional permutations

These were run in Chrome after the cold-run measurements:

| Permutation | Result | Server (s) | DB (s) | DB queries | Top 10 queries |
| --- | --- | ---: | ---: | ---: | ---: |
| Network Dashboard, cold | Two widgets rendered | 0.337130 | 0.051032 | 765 | 30 |
| Network Dashboard, warm | Cached widgets rendered | 0.298044 | 0.037499 | 742 | 6 |
| Network Dashboard with query string | Cached widgets rendered | 0.319259 | 0.034907 | 742 | 6 |
| Network popular posts, total descending | 50 rows rendered | 0.296949 | 0.039536 | 198 | 130 |
| Network popular posts, total ascending, page 2 | 50 rows rendered | 0.271374 | 0.029582 | 182 | 114 |
| Network popular posts, daily descending | 10 rows rendered | 0.226714 | 0.005042 | 87 | 19 |
| Network popular posts, daily ascending | 10 rows rendered | 0.232870 | 0.005211 | 87 | 19 |
| Network popular posts, custom date 2026-01-01 | 10 rows rendered | 0.265308 | 0.039038 | 182 | 114 |
| Network Tools, live table check | Four tables reported installed; 4 live checks | 0.276087 | 0.055291 | 89 | 20 |
| Site dashboard control | Dashboard rendered | 1.106337 | 0.018768 | 819 | 24 |

The sitewide count invalidation was also checked: a `set_count()` mutation made the next network dashboard request cold again; the following request was warm and reused the cache.

## Data correctness checks

- A seeded single-day test included rows at `00:00:00` and `23:00:00`, plus another blog with a next-day row. Both the network statistics query and `Database::get_counts_with_posts()` returned the complete same-day total and excluded the next-day row.
- Network total and daily ordering returned the expected post/blog pair and both count fields.
- Chrome showed no fatal or warning text on the Network Dashboard, Network Popular Posts, daily view, or Tools page.
- The Tools page retained live diagnostics; normal admin pages use the versioned `tptn_tables_installed` network option instead of repeating the four checks.
