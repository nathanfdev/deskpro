export const getUrlParameter = (name, url = null) => {
  const preparedName = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]'); // eslint-disable-line no-useless-escape
  const regex = new RegExp(`[\\?&]${preparedName}=([^&#]*)`);
  const results = regex.exec(url || location.search);
  return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};
