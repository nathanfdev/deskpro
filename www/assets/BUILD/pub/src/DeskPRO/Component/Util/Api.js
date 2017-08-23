/**
 * Transforms responses object from batch API call result from {0: {data: 'data'}} to {0: 'data'}
 *
 * @param {{}} responses Batch API result.responses object
 * @returns {{}} Flattened object
 */
export function flattenBatchResponses(responses) {
  const result = {};
  Object.keys(responses).forEach((key) => {
    result[key] = responses[key].data;
    return null;
  });

  return result;
}
export function replaceIds(records, field) {
  if (Array.isArray(records)) {
    return records.map((record) => {
      record.id = record[field];
      return record;
    });
  }
  Object.keys(records).forEach((key) => {
    records[key].id = records[key][field];
  });
  return records;
}

export function getLinkedData(responses, key, linkedKey) {
  return responses[key].linked[linkedKey];
}
