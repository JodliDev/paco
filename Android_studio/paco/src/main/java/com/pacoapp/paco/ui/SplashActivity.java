package com.pacoapp.paco.ui;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.google.android.apps.paco.AccountChooser;
import com.google.android.gms.auth.GooglePlayServicesAvailabilityException;
import com.google.android.gms.auth.UserRecoverableAuthException;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.pacoapp.paco.R;
import com.pacoapp.paco.UserPreferences;
import com.pacoapp.paco.net.AbstractAuthTokenTask;
import com.pacoapp.paco.net.GetAuthTokenInForeground;
import com.pacoapp.paco.net.NetworkClient;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.accounts.AccountManagerCallback;
import android.accounts.AccountManagerFuture;
import android.accounts.OperationCanceledException;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.Build;
import android.os.Bundle;
import android.support.v7.app.AlertDialog;
import android.text.InputType;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import java.sql.Timestamp;

public class SplashActivity extends Activity implements NetworkClient {

  private static Logger Log = LoggerFactory.getLogger(SplashActivity.class);

  public static final String EXTRA_ACCOUNTNAME = "extra_accountname";
  public static final String EXTRA_CHANGING_EXISTING_ACCOUNT = "extra_changing_existing_account";

  public static final int REQUEST_CODE_PICK_ACCOUNT = 1000;
  public static final int REQUEST_CODE_RECOVER_FROM_AUTH_ERROR = 1001;
  public static final int REQUEST_CODE_RECOVER_FROM_PLAY_SERVICES_ERROR = 1002;

  protected static final int ACCOUNT_CHOOSER_REQUEST_CODE = 55;


  private UserPreferences userPrefs;
  private boolean changingExistingAccount;

  @Override
  protected void onCreate(Bundle savedInstanceState) {
    super.onCreate(savedInstanceState);
    setContentView(R.layout.splash_screen);
    Log.debug("SplashActivity onCreate()");

    changingExistingAccount = getIntent().getBooleanExtra(EXTRA_CHANGING_EXISTING_ACCOUNT, false);

    userPrefs = new UserPreferences(getApplicationContext());

    Button loginButton = (Button)findViewById(R.id.loginButton);
    loginButton.setOnClickListener(new View.OnClickListener() {

      @SuppressLint("NewApi")
      @Override
      public void onClick(View v) {
        authenticateUser();
      }
    });
  }

  @Override
  protected void onActivityResult(int requestCode, int resultCode, Intent data) {
      if (requestCode == REQUEST_CODE_PICK_ACCOUNT) {
          if (resultCode == RESULT_OK) {
              userPrefs.saveSelectedAccount(data.getStringExtra(AccountManager.KEY_ACCOUNT_NAME));
              changingExistingAccount = false; // unset so that we don't loop in the picker forever
              authenticateUser();
          } else if (resultCode == RESULT_CANCELED) {
              Toast.makeText(this, R.string.you_must_pick_an_account, Toast.LENGTH_SHORT).show();
          }
      } else if ((requestCode == REQUEST_CODE_RECOVER_FROM_AUTH_ERROR ||
              requestCode == REQUEST_CODE_RECOVER_FROM_PLAY_SERVICES_ERROR)
              && resultCode == RESULT_OK) {
          handleAuthorizeResult(resultCode, data);
          return;
      }
      super.onActivityResult(requestCode, resultCode, data);
  }

  private void handleAuthorizeResult(int resultCode, Intent data) {
    if (data == null) {
        show("Unknown error, click the button again");
        return;
    }
    if (resultCode == RESULT_OK) {
        Log.info("Retrying");
        getTask(this).execute();
        return;
    }
    if (resultCode == RESULT_CANCELED) {
        show("User rejected authorization.");
        return;
    }
    show("Unknown error, click the button again");
  }

  private void setAccessToken(String token) {
    userPrefs.setAccessToken(token);

  }

  private String getAccessToken() {
    return userPrefs.getAccessToken();
  }

  @Override
  protected void onResume() {
    super.onResume();

    if (changingExistingAccount) { //FORK
      authenticateUser();
    }

    //handle case of broken Google Play Services
    // TODO remove when we get a build that properly incorporates Google Play Services and resources
    // and can build an apk with < 64k methods for Android < 5.0 phones
//FORK    int resultCode = GooglePlayServicesUtil.isGooglePlayServicesAvailable(getApplicationContext());
//
//    if (resultCode != ConnectionResult.SUCCESS) {
//      try {
//        // if the class that Paco doesn't provide is not on the system, don't
//        // use it to show an error dialog. Instead make a toast or dialog.
//        SplashActivity.this.getClassLoader().loadClass("com.google.android.gms.common.R$string");
//        Dialog dialog = GooglePlayServicesUtil.getErrorDialog(resultCode,
//                                                              SplashActivity.this,
//                                                              REQUEST_CODE_RECOVER_FROM_PLAY_SERVICES_ERROR);
//                                                      dialog.show();
//      } catch (ClassNotFoundException e) {
//        Toast.makeText(getApplicationContext(),
//                       "GooglePlayServices " + getString(R.string.are_not_available_) + " " + getString(R.string.error) + ":\n" + getGooglePlayConnectionErrorString(resultCode),
//                       Toast.LENGTH_LONG).show();
//      }
//    } else {
//      if (changingExistingAccount) {
//        authenticateUser();
//      }
//    }
  }

  public void authenticateUser() {
    if (userPrefs.getSelectedAccount() == null || changingExistingAccount) {
      pickUserAccount();
    }

    AlertDialog.Builder builder = new AlertDialog.Builder(this);

    builder.setTitle(R.string.provide_access_key_title);
    builder.setMessage(R.string.provide_access_key);
    final EditText access_key_input = new EditText(this);
    access_key_input.setInputType(InputType.TYPE_CLASS_TEXT);
    builder.setView(access_key_input);

    builder.setPositiveButton(R.string.ok, new DialogInterface.OnClickListener() {
      public void onClick(DialogInterface dialog, int id) {
        userPrefs.saveAccessKey(access_key_input.getText().toString());
        getTask(SplashActivity.this).execute();
      }
    });
    builder.setNegativeButton(R.string.cancel_button, new DialogInterface.OnClickListener() {
      public void onClick(DialogInterface dialog, int id) {
      }
    });
    builder.create().show();
//
//
//      if (isDeviceOnline()) {
//        getTask(this).execute();
//      } else {
//        Toast.makeText(this, getString(R.string.network_required), Toast.LENGTH_LONG).show();
//      }
  }

  private AbstractAuthTokenTask getTask(SplashActivity activity) {
    return new GetAuthTokenInForeground(activity);
  }


  @SuppressLint("NewApi")
  public void pickUserAccount() {
    //FORK>
    Timestamp t = new Timestamp(System.currentTimeMillis());
    String username = String.valueOf(Math.round(t.getTime()/1000)%10000000)+"." + String.valueOf(Math.round(Math.random()*1000));
    userPrefs.saveSelectedAccount(username);
    Log.debug("Fork - Account is: " + username);
    //<FORK
//FORK    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
//      Account account = null;
//      if (userPrefs.getSelectedAccount() != null) {
//        account = getAccountFor(userPrefs.getSelectedAccount());
//      }
//      Intent intent = AccountManager.newChooseAccountIntent(account, null,
//                                                            new String[]{"com.google"},
//                                                            changingExistingAccount,
//                                                            null,
//                                                            AbstractAuthTokenTask.AUTH_TOKEN_TYPE_USERINFO_EMAIL,
//                                                            null, null);
//      startActivityForResult(intent, REQUEST_CODE_PICK_ACCOUNT);
//    } else {
//      Intent intent = new Intent(SplashActivity.this, AccountChooser.class);
//      startActivityForResult(intent, REQUEST_CODE_PICK_ACCOUNT);
//    }
  }

  /** Checks whether the device currently has a network connection */
  private boolean isDeviceOnline() {
    ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
    NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
    if (networkInfo != null && networkInfo.isConnected()) {
      return true;
    }
    return false;
  }

  public void show(final String message) {
    runOnUiThread(new Runnable() {
        @Override
        public void run() {
            Toast.makeText(SplashActivity.this, message, Toast.LENGTH_LONG);
        }
    });
}

  @Override
  public void handleException(final Exception e) {
    runOnUiThread(new Runnable() {
        @Override
        public void run() {
            if (e instanceof GooglePlayServicesAvailabilityException) {
                // The Google Play services APK is old, disabled, or not present.
                // Show a dialog created by Google Play services that allows
                // the user to update the APK
                int statusCode = ((GooglePlayServicesAvailabilityException)e)
                        .getConnectionStatusCode();

                try {
                   // TODO remove this when we can build Google Play Services in properly
                  // if the class that Paco doesn't provide is not on the system, don't
                  // use it to show an error dialog. Instead make a toast or dialog.
                  SplashActivity.this.getClassLoader().loadClass("com.google.android.gms.common.R$string");
                  Dialog dialog = GooglePlayServicesUtil.getErrorDialog(statusCode,
                                                                        SplashActivity.this,
                                                                        REQUEST_CODE_RECOVER_FROM_PLAY_SERVICES_ERROR);
                                                                dialog.show();
                } catch (ClassNotFoundException e) {
                  String gpsError = getGooglePlayConnectionErrorString(statusCode);
                  Toast.makeText(getApplicationContext(),
                               getString(R.string.error) + ": " + gpsError,
                               Toast.LENGTH_LONG).show();
                }

            } else if (e instanceof UserRecoverableAuthException) {
                // Unable to authenticate, such as when the user has not yet granted
                // the app access to the account, but the user can fix this.
                // Forward the user to an activity in Google Play services.
                Intent intent = ((UserRecoverableAuthException)e).getIntent();
                startActivityForResult(intent,
                        REQUEST_CODE_RECOVER_FROM_PLAY_SERVICES_ERROR);
            }
        }

         });
}

  public String getGooglePlayConnectionErrorString(int statusCode) {
    String gpsError = "unknown";
    switch(statusCode) {
    case ConnectionResult.API_UNAVAILABLE:
      gpsError = "API Unavailable";
      break;
    case ConnectionResult.CANCELED:
      gpsError = "Canceled";
      break;
    case ConnectionResult.DEVELOPER_ERROR:
      gpsError = "Developer Error";
      break;
    case ConnectionResult.INTERNAL_ERROR:
      gpsError = "Internal error";
      break;
    case ConnectionResult.INTERRUPTED:
      gpsError = "Interrupted";
      break;
    case ConnectionResult.INVALID_ACCOUNT:
      gpsError = "Invalid Account";
      break;
    case ConnectionResult.LICENSE_CHECK_FAILED:
      gpsError = "License Check Failed";
      break;
    case ConnectionResult.NETWORK_ERROR:
      gpsError = "Network Error";
      break;
    case ConnectionResult.RESOLUTION_REQUIRED:
      gpsError = "Resolution Required";
      break;
    case ConnectionResult.SERVICE_DISABLED:
      gpsError = "Service Disabled";
      break;
    case ConnectionResult.SERVICE_INVALID:
      gpsError = "Service Invalid";
      break;
    case ConnectionResult.SERVICE_MISSING:
      gpsError = "Service Missing";
      break;
    case ConnectionResult.SERVICE_VERSION_UPDATE_REQUIRED:
      gpsError = "Service version update required";
      break;
    case ConnectionResult.SIGN_IN_FAILED:
      gpsError = "Sign in failed";
      break;
    case ConnectionResult.SIGN_IN_REQUIRED:
      gpsError = "Sign in required";
      break;
    case ConnectionResult.SUCCESS:
      gpsError = "Success";
      break;
    case ConnectionResult.TIMEOUT:
      gpsError = "Timeout";
      break;
    default:
      break;
    }
    return gpsError;
  }

  public void showAndFinish(String string) {
    show(string);
    finish();

  }

  @Override
  public Context getContext() {
    return this.getApplicationContext();
  }

}
