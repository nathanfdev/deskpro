export function reOrderCollection(collection, itemId, newOrder, orderKey) {
  let updatedCollection = collection;
  if (!itemId || !newOrder || !orderKey) {
    return updatedCollection;
  }

  const movingItem = updatedCollection.filter(item => item.get('id') === itemId).first();
  const oldOrder = movingItem.get(orderKey);

  if (oldOrder !== newOrder) {
    const minOrder = Math.min(newOrder, oldOrder);
    const maxOrder = Math.max(newOrder, oldOrder);

    const movingIndex = collection.indexOf(movingItem);
    const movingOrder = oldOrder > newOrder ? newOrder + 1 : newOrder;

    updatedCollection = updatedCollection.set(movingIndex, movingItem.set(orderKey, movingOrder));
    updatedCollection
      .filter(item => item.get('id') !== movingItem.get('id'))
      .filter(item => item.get(orderKey) > minOrder && item.get(orderKey) <= maxOrder)
      .forEach(item => {
        const index = collection.indexOf(item);
        const itemOrder = item.get(orderKey) + (newOrder > oldOrder ? -1 : 1);

        updatedCollection = updatedCollection.set(index, item.set(orderKey, itemOrder));
      })
    ;
  }

  return updatedCollection;
}
