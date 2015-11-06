import React from 'react';
import Loader from 'react-loader';

export class ListItemSpinner extends React.Component {

  render() {
    return (
      <div style={{marginTop: '3px', marginBottom: '1px'}}>
        <Loader loaded={false}
                scale={0.5}
                left="50%"
                top="50%"
                color="green"
                width={3} />
      </div>
    );
  }
}
