import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from './store';

// Departments

export const allDepartmentsSelector = collectionSelectorFactory('Department', 'all');
export const myDepartmentsSelector = collectionSelectorFactory('Department', 'my');
export const departmentsLoaded = isLoadedCollectionSelectorFactory('Department', 'all');