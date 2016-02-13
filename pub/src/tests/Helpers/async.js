/**
 * Jasmine 1.3 async helper
 *
 * usage:
 * it('should be async', async(function(done) {
 *   setTimeout(done, 1000)
 * }))
 */

export function async(run) {
  return () => {
    var done = false;
    waitsFor(() => { return done; });
    run(() => { done = true; });
  };
}
