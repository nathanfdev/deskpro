import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allTicketDepartmentsSelector = collectionSelectorFactory('TicketDepartment', 'all');
export const selectableTicketDepartmentsSelector = collectionSelectorFactory('TicketDepartment', 'selectable');
export const isTicketDepartmentsLoadedSelector = isLoadedCollectionSelectorFactory('TicketDepartment', 'all');
