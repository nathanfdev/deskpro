
/**
 * Retrieves the sidebar state
 *
 * @return string|null
 */
export default function getSidebarState() {
  if (!localStorage) {
    return null;
  }

  let state;

  try {
    state = JSON.parse(localStorage.apps_sidebar_state);
    if (typeof state !== 'object') {
      console.error(`expecting apps_sidebar_state to be an object, received instead ${typeof state}`);
      return 'pinned';
    }
  } catch (e) {
    console.error('failed to parse the apps_sidebar_state', e);
    return 'pinned';
  }

  const { pinned, expanded } = state;
  if (pinned) {
    return 'pinned';
  }
  return expanded ? 'expanded' : 'collapsed';
}
