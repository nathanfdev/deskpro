import $ from 'jquery';
import 'intl-tel-input';
import 'intl-tel-input/build/js/utils';
import countries from 'countries-list/countries.json';

export const getPhoneCountryData = (number) => {
  const $input = $('<input />');
  $input.intlTelInput({
    autoPlaceholder: true,
    allowExtensions: true,
    nationalMode:    true
  });

  $input.intlTelInput('utilsLoaded');
  $input.intlTelInput('setNumber', `${number}`);

  return $input.intlTelInput('getSelectedCountryData');
};

export const getPhoneCountryCode = number => getPhoneCountryData(number).iso2.toUpperCase();
export const getPhoneCountryName = (number) => {
  const countryCode = getPhoneCountryCode(number);

  return countries.countries[countryCode] ? countries.countries[countryCode].name : '';
};
