# Performance budgets

Release testing uses representative tourism data rather than empty-site measurements.

## Automated baseline

The test suite generates and reads 1,000 normalized tourism CSV rows in 100-row batches. On a standard GitHub Actions runner it must complete in less than five seconds while increasing allocated memory by less than 64 MiB.

## WordPress staging budgets

Measure with a persistent object cache both disabled and enabled:

| Scenario | Budget |
| --- | --- |
| First uncached 12-record listing | At most 35 database queries and 500 ms plugin processing time |
| Repeated identical listing | At least 80% faster plugin processing time with no tourism query rerun |
| Two independent homepage contexts | No duplicated request for an identical context; no state crossover |
| Public REST query | `per_page` never exceeds 24 |
| Context map | Marker count remains bounded by the listing result/page limits |
| CSV import | Batches remain between 5 and 100 rows; default is 25 |

Record WordPress, PHP, database, theme, builder, object-cache, and plugin versions with results. A budget regression blocks release unless it is documented and explicitly accepted.
