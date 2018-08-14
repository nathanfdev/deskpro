
/**
 * Retrieves the sidebar state
 *
 * @return string|null
 */
export default function getSidebarState() {
  if (!localStorage) {
    return null;
  }

  const state = JSON.parse(localStorage.apps_sidebar_state);
  if (typeof state !== 'object') {
    throw new Error('expecting apps_sidebar_state to be an object');
  }

  const { pinned, expanded } = state;


  if (pinned) {
    return 'pinned';
  }

  return expanded ? 'expanded' : 'collapsed';
}
