const initialState = {
	user: {person_id: 0}
};

export default function AppStore(state = initialState, action) {
	switch (action.type) {
		case 'APP_LOAD_USER':
			return {
				...state,
				user: action.user
			}
			break;
		default:
			return state;
	}
}