import { PropTypes } from 'react';
import ImmutablePropTypes from 'react-immutable-proptypes';

const count = {
  count:      PropTypes.number,
  nested:     PropTypes.object,
  id:         PropTypes.any,
  type:       PropTypes.string,
  title:      PropTypes.string,
  grouped_by: PropTypes.string
};

export const countShape               = PropTypes.shape(count);
export const countsArrayShape         = PropTypes.arrayOf(countShape);
export const immutableCountShape      = ImmutablePropTypes.mapContains(count);
export const immutableCountsListShape = ImmutablePropTypes.listOf(immutableCountShape);